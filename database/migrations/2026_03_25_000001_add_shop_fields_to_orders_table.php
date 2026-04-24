<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('delivery_zone')->nullable()->after('city');
            $table->string('delivery_address')->nullable()->after('delivery_zone');
            $table->string('delivery_city')->nullable()->after('delivery_address');
            $table->text('delivery_notes')->nullable()->after('delivery_city');
            $table->unsignedInteger('subtotal')->default(0)->after('delivery_notes');
            $table->unsignedInteger('delivery_fee')->default(0)->after('subtotal');
            $table->boolean('paid')->default(false)->after('delivery_fee');
            $table->string('payment_method')->nullable()->after('paid');
            $table->timestamp('placed_at')->nullable()->after('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_zone',
                'delivery_address',
                'delivery_city',
                'delivery_notes',
                'subtotal',
                'delivery_fee',
                'paid',
                'payment_method',
                'placed_at',
            ]);
        });
    }
};
