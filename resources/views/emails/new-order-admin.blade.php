<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Nouvelle commande</title>
<style>
  body { margin:0; padding:0; background:#f5f5f5; font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; color:#333; }
  .wrapper { max-width:600px; margin:32px auto; background:#fff; border:1px solid #e5e5e5; }
  .header { background:#0d0d0d; padding:24px 40px; }
  .header h1 { margin:0; color:#c9a96e; font-size:18px; letter-spacing:0.12em; text-transform:uppercase; }
  .header p { margin:6px 0 0; color:#777; font-size:12px; }
  .body { padding:36px 40px; }
  .badge { display:inline-block; background:#c9a96e; color:#fff; font-size:11px; font-weight:700; letter-spacing:0.15em; text-transform:uppercase; padding:5px 14px; margin-bottom:24px; }
  .kv { margin-bottom:8px; font-size:13px; }
  .kv strong { display:inline-block; min-width:140px; color:#666; font-weight:600; }
  table.items { width:100%; border-collapse:collapse; margin:24px 0; }
  table.items th { background:#f5f5f5; font-size:11px; letter-spacing:0.1em; text-transform:uppercase; color:#666; padding:10px 12px; text-align:left; border-bottom:2px solid #e5e5e5; }
  table.items td { padding:10px 12px; font-size:13px; border-bottom:1px solid #f0f0f0; }
  .total-row td { font-weight:700; font-size:14px; border-top:2px solid #0d0d0d; padding-top:14px; }
  .cta { text-align:center; margin:28px 0; }
  .cta a { background:#0d0d0d; color:#c9a96e; text-decoration:none; font-size:11px; font-weight:700; letter-spacing:0.2em; text-transform:uppercase; padding:14px 32px; display:inline-block; }
  .footer { background:#f5f5f5; padding:20px 40px; border-top:1px solid #e5e5e5; text-align:center; }
  .footer p { margin:0; font-size:11px; color:#999; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>Nouvelle commande reçue</h1>
    <p>{{ now()->format('d/m/Y à H:i') }}</p>
  </div>

  <div class="body">
    <span class="badge">À traiter</span>

    <div class="kv"><strong>Référence</strong> {{ $order->order_number }}</div>
    <div class="kv"><strong>Client</strong> {{ $order->customer_name }}</div>
    <div class="kv"><strong>Téléphone</strong> {{ $order->customer_phone }}</div>
    @if($order->customer_email)
    <div class="kv"><strong>Email</strong> {{ $order->customer_email }}</div>
    @endif
    <div class="kv"><strong>Adresse</strong> {{ $order->customer_address }}{{ $order->city ? ', '.$order->city : '' }}</div>
    <div class="kv"><strong>Paiement</strong> {{ match($order->payment_method) { 'cash' => 'Paiement à la livraison', 'wave' => 'Wave Mobile Money', 'intech' => 'Intech (carte)', default => $order->payment_method } }}</div>
    @if($order->delivery_notes)
    <div class="kv"><strong>Notes</strong> {{ $order->delivery_notes }}</div>
    @endif

    <table class="items">
      <thead>
        <tr>
          <th>Article</th>
          <th style="text-align:right">Qté</th>
          <th style="text-align:right">Prix unit.</th>
          <th style="text-align:right">Total</th>
        </tr>
      </thead>
      <tbody>
        @foreach($order->items as $item)
        <tr>
          <td>{{ $item->product_name }}</td>
          <td style="text-align:right">{{ $item->quantity }}</td>
          <td style="text-align:right">{{ number_format($item->price, 0, ',', ' ') }} FCFA</td>
          <td style="text-align:right">{{ number_format($item->price * $item->quantity, 0, ',', ' ') }} FCFA</td>
        </tr>
        @endforeach
        <tr>
          <td colspan="3" style="text-align:right;font-size:12px;color:#666;">Livraison</td>
          <td style="text-align:right;font-size:13px;">{{ $order->delivery_fee > 0 ? number_format($order->delivery_fee, 0, ',', ' ').' FCFA' : 'Gratuite' }}</td>
        </tr>
        <tr class="total-row">
          <td colspan="3" style="text-align:right">Total</td>
          <td style="text-align:right">{{ number_format($order->total, 0, ',', ' ') }} FCFA</td>
        </tr>
      </tbody>
    </table>

    <div class="cta">
      <a href="{{ route('admin.orders.show', $order) }}">Voir dans l'admin</a>
    </div>
  </div>

  <div class="footer">
    <p>Notification automatique — Mams Store World</p>
  </div>
</div>
</body>
</html>
