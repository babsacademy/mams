<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Commande confirmée</title>
<style>
  body { margin:0; padding:0; background:#f5f5f5; font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; color:#333; }
  .wrapper { max-width:600px; margin:32px auto; background:#fff; border:1px solid #e5e5e5; }
  .header { background:#0d0d0d; padding:32px 40px; text-align:center; }
  .header h1 { margin:0; color:#c9a96e; font-size:22px; letter-spacing:0.15em; text-transform:uppercase; }
  .header p { margin:8px 0 0; color:#999; font-size:12px; letter-spacing:0.1em; }
  .body { padding:40px; }
  .greeting { font-size:16px; margin-bottom:24px; }
  .order-box { background:#f9f9f9; border:1px solid #e5e5e5; padding:20px 24px; margin-bottom:28px; }
  .order-box .ref { font-size:20px; font-weight:700; letter-spacing:0.1em; color:#0d0d0d; }
  .order-box .status { display:inline-block; margin-top:8px; background:#c9a96e; color:#fff; font-size:11px; font-weight:700; letter-spacing:0.15em; text-transform:uppercase; padding:4px 12px; }
  table.items { width:100%; border-collapse:collapse; margin-bottom:20px; }
  table.items th { background:#0d0d0d; color:#c9a96e; font-size:11px; letter-spacing:0.1em; text-transform:uppercase; padding:10px 12px; text-align:left; }
  table.items td { border-bottom:1px solid #f0f0f0; padding:12px; font-size:13px; }
  table.items tr:last-child td { border-bottom:none; }
  .totals { width:100%; }
  .totals td { padding:6px 0; font-size:13px; }
  .totals td:last-child { text-align:right; font-weight:600; }
  .totals .grand-total td { font-size:15px; font-weight:700; border-top:2px solid #0d0d0d; padding-top:12px; }
  .info-block { margin-bottom:24px; }
  .info-block h3 { font-size:11px; letter-spacing:0.15em; text-transform:uppercase; color:#999; margin-bottom:10px; }
  .info-block p { margin:0; font-size:13px; line-height:1.6; }
  .cta { text-align:center; margin:32px 0; }
  .cta a { background:#0d0d0d; color:#c9a96e; text-decoration:none; font-size:11px; font-weight:700; letter-spacing:0.2em; text-transform:uppercase; padding:14px 32px; display:inline-block; }
  .footer { background:#f5f5f5; padding:24px 40px; text-align:center; border-top:1px solid #e5e5e5; }
  .footer p { margin:0; font-size:11px; color:#999; line-height:1.7; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>{{ $order->customer_name ? explode(' ', $order->customer_name)[0] : 'Cher client' }}, merci !</h1>
    <p>Votre commande a bien été reçue</p>
  </div>

  <div class="body">
    <p class="greeting">Bonjour <strong>{{ $order->customer_name }}</strong>,</p>
    <p style="font-size:14px;line-height:1.7;margin-bottom:28px;">
      Nous avons bien reçu votre commande et elle est en cours de traitement. Vous serez contacté(e) rapidement pour confirmer la livraison.
    </p>

    <div class="order-box">
      <div style="font-size:11px;letter-spacing:0.1em;text-transform:uppercase;color:#999;margin-bottom:4px;">Référence</div>
      <div class="ref">{{ $order->order_number }}</div>
      <div><span class="status">En attente</span></div>
    </div>

    <table class="items">
      <thead>
        <tr>
          <th>Article</th>
          <th style="text-align:right">Qté</th>
          <th style="text-align:right">Prix</th>
        </tr>
      </thead>
      <tbody>
        @foreach($order->items as $item)
        <tr>
          <td>{{ $item->product_name }}</td>
          <td style="text-align:right">{{ $item->quantity }}</td>
          <td style="text-align:right">{{ number_format($item->price * $item->quantity, 0, ',', ' ') }} FCFA</td>
        </tr>
        @endforeach
      </tbody>
    </table>

    <table class="totals">
      <tr>
        <td>Sous-total</td>
        <td>{{ number_format($order->subtotal ?? 0, 0, ',', ' ') }} FCFA</td>
      </tr>
      <tr>
        <td>Livraison</td>
        <td>{{ $order->delivery_fee > 0 ? number_format($order->delivery_fee, 0, ',', ' ').' FCFA' : 'Gratuite' }}</td>
      </tr>
      <tr class="grand-total">
        <td>Total</td>
        <td>{{ number_format($order->total, 0, ',', ' ') }} FCFA</td>
      </tr>
    </table>

    <hr style="border:none;border-top:1px solid #f0f0f0;margin:28px 0;">

    <div class="info-block">
      <h3>Livraison</h3>
      <p>{{ $order->customer_address }}<br>
      @if($order->city){{ $order->city }}<br>@endif
      @if($order->delivery_notes)<em>{{ $order->delivery_notes }}</em>@endif</p>
    </div>

    <div class="info-block">
      <h3>Mode de paiement</h3>
      <p>{{ match($order->payment_method) { 'cash' => 'Paiement à la livraison', 'wave' => 'Wave Mobile Money', 'intech' => 'Intech (carte bancaire)', default => $order->payment_method } }}</p>
    </div>

    <div class="cta">
      <a href="{{ route('home') }}">Continuer mes achats</a>
    </div>
  </div>

  <div class="footer">
    <p>Des questions ? Contactez-nous sur WhatsApp ou par email.<br>
    Merci de votre confiance — <strong style="color:#0d0d0d;">Mams Store World</strong></p>
  </div>
</div>
</body>
</html>
