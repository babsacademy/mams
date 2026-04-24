const STOREFRONT_BRAND = window.STOREFRONT_BRAND || 'Mams Store World';
const STOREFRONT_WHATSAPP = window.STOREFRONT_WHATSAPP || '221771831987';

function generateOrderNumber() {
    const prefix = 'MS';
    const random1 = Math.floor(100 + Math.random() * 900);
    const random2 = Math.floor(1000 + Math.random() * 9000);

    return prefix + '-' + random1 + '-' + random2;
}

function generateDirectWhatsAppLink(productName, price, size, color, orderNumber) {
    const orderRef = orderNumber || generateOrderNumber();
    let message = `Bonjour ${STOREFRONT_BRAND}, je souhaite commander cet article.\n`;
    message += `Reference : ${orderRef}\n`;
    message += `Produit : ${productName}\n`;
    message += `Option : ${size || 'Standard'}\n`;
    message += `Couleur : ${color || 'Selon disponibilite'}\n`;
    message += `Prix : ${price} FCFA\n`;

    return `https://wa.me/${STOREFRONT_WHATSAPP}?text=${encodeURIComponent(message)}`;
}

function formatPrice(price) {
    return new Intl.NumberFormat('fr-FR').format(price) + ' FCFA';
}

window.generateOrderNumber = generateOrderNumber;
window.generateDirectWhatsAppLink = generateDirectWhatsAppLink;
window.formatPrice = formatPrice;
