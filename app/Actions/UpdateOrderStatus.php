<?php

namespace App\Actions;

use App\Mail\OrderStatusUpdated;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class UpdateOrderStatus
{
    public function execute(Order $order, string $newStatus): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        if (! array_key_exists($newStatus, Order::STATUSES)) {
            return;
        }

        DB::transaction(function () use ($order, $newStatus): void {
            // Re-fetch the order with a pessimistic lock to prevent concurrent modifications.
            $fresh = Order::lockForUpdate()->findOrFail($order->id);

            // Idempotency guard: if already at this status, do nothing.
            if ($fresh->status === $newStatus) {
                return;
            }

            // Restore stock when transitioning INTO cancelled from a non-cancelled state.
            if ($newStatus === 'cancelled' && $fresh->status !== 'cancelled') {
                $fresh->loadMissing('items');

                foreach ($fresh->items as $item) {
                    if ($item->product_id) {
                        Product::lockForUpdate()
                            ->where('id', $item->product_id)
                            ->increment('stock', $item->quantity);
                    }
                }
            }

            $fresh->update(['status' => $newStatus]);
        });

        $order->refresh();
        $this->sendStatusEmail($order);
    }

    private function sendStatusEmail(Order $order): void
    {
        if (! config('mail.mailers.smtp.host') || ! $order->customer_email) {
            return;
        }

        Mail::to($order->customer_email)->send(new OrderStatusUpdated($order));
    }
}
