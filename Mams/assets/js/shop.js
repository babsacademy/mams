(() => {
  const state = {
    type: "all",
    texture: "all",
    color: "all",
    sort: "new",
    visible: 6,
    query: new URLSearchParams(window.location.search).get("q") || ""
  };

  const root = document.querySelector("[data-shop-root]");
  if (!root) {
    return;
  }

  const data = (window.MAMS_DATA && window.MAMS_DATA.PRODUCTS) || [];
  const grid = document.querySelector("[data-products-grid]");
  const countNode = document.querySelector("[data-products-count]");
  const loadMoreBtn = document.querySelector("[data-load-more]");
  const colorContainer = document.querySelector("[data-color-filters]");

  function formatPrice(value) {
    return window.MAMS_UTILS.formatPrice(value);
  }

  function typeLabel(type) {
    return type === "human_hair" ? "Human Hair" : "Blend Hair";
  }

  function getFilteredProducts() {
    const normalizedQuery = state.query.trim().toLowerCase();
    let result = data.filter((product) => {
      const byType =
        state.type === "all" ||
        product.laceTypes.some((lace) => lace.toLowerCase() === state.type.toLowerCase());
      const byTexture =
        state.texture === "all" ||
        product.textures.some((texture) => texture.toLowerCase() === state.texture.toLowerCase());
      const byColor =
        state.color === "all" ||
        product.colors.some((color) => color.toLowerCase() === state.color.toLowerCase());
      const byQuery =
        !normalizedQuery ||
        [product.name, product.description, product.type, ...(product.colors || []), ...(product.textures || []), ...(product.laceTypes || [])]
          .join(" ")
          .toLowerCase()
          .includes(normalizedQuery);
      return byType && byTexture && byColor && byQuery;
    });

    if (state.sort === "price_asc") {
      result = [...result].sort((a, b) => a.price - b.price);
    } else if (state.sort === "price_desc") {
      result = [...result].sort((a, b) => b.price - a.price);
    } else {
      result = [...result].sort((a, b) => Number(b.isNew) - Number(a.isNew));
    }

    return result;
  }

  function cardTemplate(product) {
    const badge = product.badge
      ? `<span class="absolute left-3 top-3 rounded-full bg-[#c9a96e] px-3 py-1 text-[10px] label-caps text-black">${product.badge}</span>`
      : "";
    return `
      <article class="group rounded-2xl border border-white/10 bg-[#111] p-3 hover-lift">
        <div class="product-card-image rounded-xl">
          <div class="img-primary placeholder-media rounded-xl"></div>
          <div class="img-secondary placeholder-media rounded-xl"></div>
          ${badge}
        </div>
        <div class="pt-4 space-y-3">
          <a href="product.html?id=${product.id}" class="block font-display text-2xl leading-tight hover:text-[#c9a96e] transition">${product.name}</a>
          <p class="text-xs label-caps text-[#c9a96e]">${typeLabel(product.type)}</p>
          <p class="text-lg font-semibold">${formatPrice(product.price)}</p>
          <div class="flex gap-2">
            <button data-add-cart="${product.id}" class="flex-1 h-11 rounded-full bg-[#c9a96e] text-black text-xs font-semibold tracking-[0.12em] uppercase hover:brightness-110 transition">
              Ajouter au panier
            </button>
            <button class="h-11 w-11 rounded-full border border-white/20 text-[#f5f0e8] hover:border-[#c9a96e] hover:text-[#c9a96e] transition">♡</button>
          </div>
        </div>
      </article>
    `;
  }

  function renderProducts() {
    const products = getFilteredProducts();
    const visibleItems = products.slice(0, state.visible);
    grid.innerHTML = visibleItems.map(cardTemplate).join("");
    const queryLabel = state.query.trim() ? ` pour “${state.query.trim()}”` : "";
    countNode.textContent = `${products.length} résultat${products.length > 1 ? "s" : ""}${queryLabel}`;
    loadMoreBtn.classList.toggle("hidden", state.visible >= products.length);
    bindAddButtons(visibleItems);
  }

  function bindAddButtons(visibleItems) {
    root.querySelectorAll("[data-add-cart]").forEach((button) => {
      button.addEventListener("click", () => {
        const id = Number(button.dataset.addCart);
        const product = visibleItems.find((entry) => entry.id === id) || data.find((entry) => entry.id === id);
        if (!product) {
          return;
        }
        const variation = {
          length: product.lengths[0],
          color: product.colors[0],
          texture: product.textures[0],
          laceType: product.laceTypes[0]
        };
        window.MamsCart.addToCart(product, variation, 1);
      });
    });
  }

  function initControls() {
    root.querySelectorAll("[data-filter-type]").forEach((button) => {
      button.addEventListener("click", () => {
        state.type = button.dataset.filterType;
        state.visible = 6;
        root.querySelectorAll("[data-filter-type]").forEach((el) => el.classList.remove("bg-[#c9a96e]", "text-black"));
        button.classList.add("bg-[#c9a96e]", "text-black");
        renderProducts();
      });
    });

    root.querySelectorAll("[data-filter-texture]").forEach((button) => {
      button.addEventListener("click", () => {
        state.texture = button.dataset.filterTexture;
        state.visible = 6;
        root.querySelectorAll("[data-filter-texture]").forEach((el) => el.classList.remove("border-[#c9a96e]", "text-[#c9a96e]"));
        button.classList.add("border-[#c9a96e]", "text-[#c9a96e]");
        renderProducts();
      });
    });

    document.querySelector("[data-sort-select]").addEventListener("change", (event) => {
      state.sort = event.target.value;
      renderProducts();
    });

    loadMoreBtn.addEventListener("click", () => {
      state.visible += 3;
      renderProducts();
    });
  }

  function initColorFilters() {
    const colors = [...new Set(data.flatMap((item) => item.colors))];
    const colorMap = {
      "Noir naturel": "#131313",
      "Brun chocolat": "#4b2e21",
      "Blonde miel": "#c89b62",
      "Brun foncé": "#2f2118",
      Caramel: "#aa6c39",
      Blonde: "#d8bb8b",
      "Brun caramel": "#76472b",
      "Blonde caramel": "#d2a16b",
      Espresso: "#2b1d14"
    };
    colorContainer.innerHTML = `
      <button data-filter-color="all" class="h-8 px-4 rounded-full border border-white/20 text-xs tracking-[0.12em] uppercase">Toutes</button>
      ${colors
        .map(
          (color) => `
        <button title="${color}" data-filter-color="${color.toLowerCase()}" class="h-8 w-8 rounded-full border border-white/20" style="background:${colorMap[color] || "#666"}"></button>
      `
        )
        .join("")}
    `;

    colorContainer.querySelectorAll("[data-filter-color]").forEach((button) => {
      button.addEventListener("click", () => {
        state.color = button.dataset.filterColor;
        state.visible = 6;
        colorContainer.querySelectorAll("[data-filter-color]").forEach((el) => el.classList.remove("ring-2", "ring-[#c9a96e]"));
        button.classList.add("ring-2", "ring-[#c9a96e]");
        renderProducts();
      });
    });
  }

  initControls();
  initColorFilters();
  renderProducts();
})();
