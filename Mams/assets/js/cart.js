const MamsCart = (() => {
  const CART_KEY = (window.MAMS_UTILS && window.MAMS_UTILS.cartKey) || "mams_cart";
  let toastTimer = null;

  function read() {
    try {
      const value = localStorage.getItem(CART_KEY);
      const data = value ? JSON.parse(value) : [];
      return Array.isArray(data) ? data : [];
    } catch (error) {
      return [];
    }
  }

  function write(items) {
    localStorage.setItem(CART_KEY, JSON.stringify(items));
    if (window.MAMS_UTILS) {
      window.MAMS_UTILS.setCartBadge();
    }
  }

  function itemKey(item) {
    const v = item.variation || {};
    return `${item.productId}-${v.length || ""}-${v.color || ""}-${v.texture || ""}-${v.laceType || ""}`;
  }

  function addToCart(product, variation = {}, qty = 1) {
    const items = read();
    const payload = {
      productId: product.id,
      name: product.name,
      price: Number(product.price),
      qty: Math.max(1, Number(qty) || 1),
      variation,
      imageLabel: product.name
    };
    const key = itemKey(payload);
    const existing = items.find((entry) => itemKey(entry) === key);
    if (existing) {
      existing.qty += payload.qty;
    } else {
      items.push(payload);
    }
    write(items);
    showToast("Ajouté au panier ✓");
    return items;
  }

  function removeFromCart(index) {
    const items = read();
    items.splice(index, 1);
    write(items);
    return items;
  }

  function updateQty(index, qty) {
    const items = read();
    if (!items[index]) {
      return items;
    }
    items[index].qty = Math.max(1, Number(qty) || 1);
    write(items);
    return items;
  }

  function clearCart() {
    write([]);
  }

  function getTotal() {
    return read().reduce((sum, item) => sum + item.price * item.qty, 0);
  }

  function createToastNode() {
    let node = document.getElementById("mams-toast");
    if (!node) {
      node = document.createElement("div");
      node.id = "mams-toast";
      node.className = "toast";
      node.setAttribute("role", "status");
      node.setAttribute("aria-live", "polite");
      document.body.appendChild(node);
    }
    return node;
  }

  function showToast(message) {
    const node = createToastNode();
    node.textContent = message;
    node.classList.add("is-visible");
    if (toastTimer) {
      clearTimeout(toastTimer);
    }
    toastTimer = setTimeout(() => {
      node.classList.remove("is-visible");
    }, 2000);
  }

  return {
    addToCart,
    removeFromCart,
    updateQty,
    getTotal,
    read,
    clearCart
  };
})();

window.MamsCart = MamsCart;
