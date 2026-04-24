<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Mise à jour de commande</title>
<style>
  body { margin:0; padding:0; background:#f5f5f5; font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; color:#333; }
  .wrapper { max-width:600px; margin:32px auto; background:#fff; border:1px solid #e5e5e5; }
  .header { background:#0d0d0d; padding:32px 40px; text-align:center; }
  .header h1 { margin:0; color:#c9a96e; font-size:20px; letter-spacing:0.12em; text-transform:uppercase; }
  .header p { margin:8px 0 0; color:#999; font-size:12px; letter-spacing:0.08em; }
  .body { padding:40px; }
  .status-block { text-align:center; margin:28px 0; }
  .status-label { display:inline-block; font-size:16px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; padding:12px 32px; }
  .status-pending   { background:#fef9c3; color:#854d0e; }
  .status-confirmed { background:#dcfce7; color:#166534; }
  .status-shipped   { background:#dbeafe; color:#1e40af; }
  .status-delivered { background:#f0fdf4; color:#14532d; border:2px solid #bbf7d0; }
  .status-cancelled { background:#fee2e2; color:#991b1b; }
  .order-ref { text-align:center; font-size:22px; font-weight:700; letter-spacing:0.15em; color:#0d0d0d; margin-bottom:28px; }
  .message { font-size:14px; line-height:1.7; color:#555; text-align:center; margin-bottom:28px; }
  .cta { text-align:center; margin:28px 0; }
  .cta a { background:#0d0d0d; color:#c9a96e; text-decoration:none; font-size:11px; font-weight:700; letter-spacing:0.2em; text-transform:uppercase; padding:14px 32px; display:inline-block; }
  .footer { background:#f5f5f5; padding:24px 40px; border-top:1px solid #e5e5e5; text-align:center; }
  .footer p { margin:0; font-size:11px; color:#999; line-height:1.7; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>Votre commande a été mise à jour</h1>
    <p>{{ $order->order_number }}</p>
  </div>

  <div class="body">
    <p style="font-size:14px;line-height:1.7;margin-bottom:24px;">
      Bonjour <strong>{{ $order->customer_name }}</strong>,
    </p>

    <div class="order-ref">{{ $order->order_number }}</div>

    <div class="status-block">
      <span class="status-label status-{{ $order->status }}">
        {{ $order->status_label }}
      </span>
    </div>

    <p class="message">
      @if($order->status === 'confirmed')
        Bonne nouvelle ! Votre commande a été confirmée et est en cours de préparation.
      @elseif($order->status === 'shipped')
        Votre commande est en route ! Un agent vous contactera prochainement pour organiser la livraison.
      @elseif($order->status === 'delivered')
        Votre commande a été livrée avec succès. Merci pour votre confiance !
      @elseif($order->status === 'cancelled')
        Votre commande a été annulée. Si vous avez des questions, n'hésitez pas à nous contacter.
      @else
        Le statut de votre commande a été mis à jour.
      @endif
    </p>

    <div class="cta">
      <a href="{{ route('home') }}">Retour à la boutique</a>
    </div>
  </div>

  <div class="footer">
    <p>Des questions ? Contactez-nous sur WhatsApp.<br>
    <strong style="color:#0d0d0d;">Mams Store World</strong></p>
  </div>
</div>
</body>
</html>
