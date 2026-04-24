const MAMS_CART_KEY = "mams_cart";
const MAMS_WHATSAPP_URL = "https://wa.me/221771831987";

function formatPrice(value) {
  return `${Math.round(value).toLocaleString("fr-FR")} FCFA`;
}

function getCartItems() {
  try {
    const raw = localStorage.getItem(MAMS_CART_KEY);
    const items = raw ? JSON.parse(raw) : [];
    return Array.isArray(items) ? items : [];
  } catch (error) {
    return [];
  }
}

function getCartCount() {
  return getCartItems().reduce((total, item) => total + (item.qty || 1), 0);
}

function setCartBadge() {
  const count = getCartCount();
  document.querySelectorAll("[data-cart-count]").forEach((badge) => {
    badge.textContent = count;
    badge.classList.toggle("hidden", count === 0);
  });
}

function getCurrentPage() {
  const page = window.location.pathname.split("/").pop() || "index.html";
  return page.toLowerCase();
}

function navLinkClass(targetPage) {
  const isActive = getCurrentPage() === targetPage;
  return `mams-link text-sm tracking-[0.18em] uppercase ${isActive ? "text-[#c9a96e]" : "text-[#f5f0e8] hover:text-[#c9a96e]"}`;
}

function renderNavbar() {
  const containers = document.querySelectorAll("[data-component='navbar']");
  if (!containers.length) {
    return;
  }

  const navMarkup = `
    <header class="sticky top-0 z-50 glass-dark">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-20 items-center justify-between gap-4">
          <a href="index.html" class="font-display text-3xl text-[#c9a96e]">MAMS</a>
          <nav class="hidden md:flex items-center gap-8">
            <a href="index.html" class="${navLinkClass("index.html")}">Accueil</a>
            <a href="shop.html" class="${navLinkClass("shop.html")}">Boutique</a>
            <a href="lookbook.html" class="${navLinkClass("lookbook.html")}">Lookbook</a>
            <a href="checkout.html" class="${navLinkClass("checkout.html")}">À propos</a>
          </nav>
          <div class="hidden md:flex items-center gap-4">
            <button type="button" data-search-toggle class="h-10 w-10 grid place-items-center rounded-full border border-white/15 text-[#f5f0e8] hover:border-[#c9a96e] hover:text-[#c9a96e] transition">
              <span aria-hidden="true">⌕</span>
            </button>
            <a href="cart.html" class="relative h-10 w-10 grid place-items-center rounded-full border border-white/15 text-[#f5f0e8] hover:border-[#c9a96e] hover:text-[#c9a96e] transition">
              <span aria-hidden="true">🛒</span>
              <span data-cart-count class="hidden absolute -right-1 -top-1 min-w-5 h-5 px-1 rounded-full bg-[#c9a96e] text-[10px] font-semibold text-black grid place-items-center">0</span>
            </a>
          </div>
          <button type="button" data-mobile-toggle class="md:hidden h-10 w-10 grid place-items-center rounded-full border border-white/15 text-[#f5f0e8]">
            ☰
          </button>
        </div>
      </div>
      <div data-mobile-menu class="mobile-menu fixed inset-0 z-50 bg-black/95 md:hidden">
        <div class="flex items-center justify-between px-5 py-5 border-b border-white/10">
          <a href="index.html" class="font-display text-3xl text-[#c9a96e]">MAMS</a>
          <button type="button" data-mobile-close class="h-10 w-10 grid place-items-center rounded-full border border-white/15 text-[#f5f0e8]">✕</button>
        </div>
        <nav class="px-6 pt-10 flex flex-col gap-6 text-center">
          <a href="index.html" class="label-caps text-[#f5f0e8]">Accueil</a>
          <a href="shop.html" class="label-caps text-[#f5f0e8]">Boutique</a>
          <a href="lookbook.html" class="label-caps text-[#f5f0e8]">Lookbook</a>
          <a href="checkout.html" class="label-caps text-[#f5f0e8]">À propos</a>
          <div class="flex items-center justify-center gap-4 pt-5">
            <button type="button" data-search-toggle class="h-11 w-11 grid place-items-center rounded-full border border-white/15 text-[#f5f0e8]">
              ⌕
            </button>
            <a href="cart.html" class="relative h-11 w-11 grid place-items-center rounded-full border border-white/15 text-[#f5f0e8]">
              🛒
              <span data-cart-count class="hidden absolute -right-1 -top-1 min-w-5 h-5 px-1 rounded-full bg-[#c9a96e] text-[10px] font-semibold text-black grid place-items-center">0</span>
            </a>
            <a href="${MAMS_WHATSAPP_URL}" target="_blank" rel="noopener noreferrer" class="h-11 px-4 rounded-full bg-[#25D366] text-black font-semibold text-xs tracking-[0.12em] uppercase grid place-items-center">
              WhatsApp
            </a>
          </div>
        </nav>
      </div>
    </header>
  `;

  containers.forEach((container) => {
    container.innerHTML = navMarkup;
  });
}

function getSearchProducts() {
  const items = window.MAMS_DATA && window.MAMS_DATA.PRODUCTS;
  return Array.isArray(items) ? items : [];
}

function filterProductsByQuery(query) {
  const normalized = query.trim().toLowerCase();
  if (!normalized) {
    return [];
  }
  const toLabel = (type) => (type === "human_hair" ? "human hair" : "blend hair");
  return getSearchProducts()
    .map((product) => {
      const haystack = [
        product.name,
        product.description,
        toLabel(product.type),
        ...(product.colors || []),
        ...(product.textures || []),
        ...(product.laceTypes || [])
      ]
        .join(" ")
        .toLowerCase();
      const startsWithName = product.name.toLowerCase().startsWith(normalized);
      const includes = haystack.includes(normalized);
      const score = startsWithName ? 2 : includes ? 1 : 0;
      return { product, score };
    })
    .filter((item) => item.score > 0)
    .sort((a, b) => b.score - a.score || b.product.rating - a.product.rating)
    .map((item) => item.product)
    .slice(0, 6);
}

function renderSearchModal() {
  if (document.getElementById("mams-search-modal")) {
    return;
  }

  const node = document.createElement("div");
  node.id = "mams-search-modal";
  node.className = "fixed inset-0 z-[90] hidden";
  node.innerHTML = `
    <div data-search-backdrop class="absolute inset-0 bg-black/80 backdrop-blur-sm"></div>
    <div class="relative mx-auto max-w-2xl px-4 pt-20">
      <div class="rounded-2xl border border-[#c9a96e]/35 bg-[#111] p-4 sm:p-6">
        <form data-search-form class="flex items-center gap-2 border border-white/15 rounded-full px-4 h-12">
          <span class="text-[#c9a96e]">⌕</span>
          <input data-search-input type="text" placeholder="Rechercher une perruque, texture, couleur..." class="w-full bg-transparent text-sm outline-none" />
          <button type="button" data-search-close class="text-white/70 hover:text-white">✕</button>
        </form>
        <div data-search-results class="mt-4 space-y-2"></div>
        <a data-search-all href="shop.html" class="mt-4 inline-flex text-xs tracking-[0.14em] uppercase text-[#c9a96e] hover:text-white transition">Voir toute la boutique</a>
      </div>
    </div>
  `;

  document.body.appendChild(node);

  const input = node.querySelector("[data-search-input]");
  const results = node.querySelector("[data-search-results]");
  const form = node.querySelector("[data-search-form]");
  const closeBtn = node.querySelector("[data-search-close]");
  const backdrop = node.querySelector("[data-search-backdrop]");
  const searchAll = node.querySelector("[data-search-all]");

  const close = () => {
    node.classList.add("hidden");
    document.body.classList.remove("overflow-hidden");
  };

  const open = () => {
    node.classList.remove("hidden");
    document.body.classList.add("overflow-hidden");
    input.value = "";
    results.innerHTML = "";
    searchAll.href = "shop.html";
    setTimeout(() => input.focus(), 30);
  };

  const renderResults = (query) => {
    const found = filterProductsByQuery(query);
    const queryEncoded = encodeURIComponent(query);
    searchAll.href = query.trim() ? `shop.html?q=${queryEncoded}` : "shop.html";
    if (!query.trim()) {
      results.innerHTML = `<p class="text-sm text-white/65 px-2 py-2">Commencez à taper pour rechercher un modèle.</p>`;
      return;
    }
    if (!found.length) {
      results.innerHTML = `<p class="text-sm text-white/65 px-2 py-2">Aucun résultat pour “${query}”.</p>`;
      return;
    }
    results.innerHTML = found
      .map(
        (product) => `
      <a href="product.html?id=${product.id}" class="flex items-center justify-between rounded-xl border border-white/10 bg-[#171717] px-4 py-3 hover:border-[#c9a96e] transition">
        <span class="font-medium">${product.name}</span>
        <span class="text-sm text-[#c9a96e]">${formatPrice(product.price)}</span>
      </a>
    `
      )
      .join("");
  };

  node.querySelectorAll("[data-search-toggle]").forEach((button) => {
    button.addEventListener("click", open);
  });

  document.addEventListener("click", (event) => {
    if (event.target.closest("[data-search-toggle]")) {
      open();
    }
  });

  input.addEventListener("input", () => {
    renderResults(input.value);
  });

  form.addEventListener("submit", (event) => {
    event.preventDefault();
    const value = input.value.trim();
    window.location.href = value ? `shop.html?q=${encodeURIComponent(value)}` : "shop.html";
  });

  closeBtn.addEventListener("click", close);
  backdrop.addEventListener("click", close);
  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && !node.classList.contains("hidden")) {
      close();
    }
  });
}

function renderFooter() {
  const containers = document.querySelectorAll("[data-component='footer']");
  if (!containers.length) {
    return;
  }

  const year = new Date().getFullYear();
  const footerMarkup = `
    <footer class="border-t border-white/10 bg-black mt-20">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid gap-10 md:grid-cols-3">
          <div>
            <h3 class="label-caps text-[#c9a96e] mb-4">Liens</h3>
            <ul class="space-y-3 text-[#d8d1c4]">
              <li><a href="index.html" class="hover:text-white transition">Accueil</a></li>
              <li><a href="shop.html" class="hover:text-white transition">Boutique</a></li>
              <li><a href="lookbook.html" class="hover:text-white transition">Lookbook</a></li>
              <li><a href="checkout.html" class="hover:text-white transition">Commander</a></li>
            </ul>
          </div>
          <div>
            <h3 class="label-caps text-[#c9a96e] mb-4">Contact</h3>
            <ul class="space-y-3 text-[#d8d1c4]">
              <li>Dakar, Sénégal</li>
              <li><a href="tel:+221771831987" class="hover:text-white transition">+221 77 183 19 87</a></li>
              <li><a href="${MAMS_WHATSAPP_URL}" class="hover:text-white transition">Commander via WhatsApp</a></li>
            </ul>
          </div>
          <div>
            <h3 class="label-caps text-[#c9a96e] mb-4">Réseaux</h3>
            <ul class="space-y-3 text-[#d8d1c4]">
              <li><a href="#" class="hover:text-white transition">@Maamanjoop</a></li>
              <li><a href="#" class="hover:text-white transition">@mams_store0</a></li>
            </ul>
          </div>
        </div>
        <div class="mt-10 border-t border-white/10 pt-6 flex flex-col gap-2 sm:flex-row sm:justify-between text-xs text-[#9f9584] tracking-[0.12em] uppercase">
          <p>© ${year} Mams Store World</p>
          <p>Site réalisé par Babsacademy</p>
        </div>
      </div>
    </footer>
  `;

  containers.forEach((container) => {
    container.innerHTML = footerMarkup;
  });
}

function setupMobileMenu() {
  const toggle = document.querySelector("[data-mobile-toggle]");
  const close = document.querySelector("[data-mobile-close]");
  const panel = document.querySelector("[data-mobile-menu]");
  if (!toggle || !close || !panel) {
    return;
  }
  const open = () => panel.classList.add("is-open");
  const hide = () => panel.classList.remove("is-open");
  toggle.addEventListener("click", open);
  close.addEventListener("click", hide);
  panel.querySelectorAll("a").forEach((link) => link.addEventListener("click", hide));
}

function renderWhatsAppFloat() {
  const placeholders = document.querySelectorAll("[data-component='whatsapp-float']");
  if (!placeholders.length) {
    return;
  }

  placeholders.forEach((node) => {
    node.innerHTML = `
      <a href="${MAMS_WHATSAPP_URL}" target="_blank" rel="noopener noreferrer" class="whatsapp-float" aria-label="WhatsApp Mams Store">
        <span class="text-xl">✆</span>
      </a>
    `;
  });
}

window.MAMS_UTILS = {
  formatPrice,
  getCartItems,
  setCartBadge,
  cartKey: MAMS_CART_KEY,
  whatsappUrl: MAMS_WHATSAPP_URL
};

document.addEventListener("DOMContentLoaded", () => {
  renderNavbar();
  renderFooter();
  renderWhatsAppFloat();
  setupMobileMenu();
  renderSearchModal();
  setCartBadge();
});
