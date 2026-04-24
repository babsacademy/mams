(() => {
  const root = document.querySelector("[data-product-root]");
  if (!root) {
    return;
  }

  const products = (window.MAMS_DATA && window.MAMS_DATA.PRODUCTS) || [];
  const reviews = (window.MAMS_DATA && window.MAMS_DATA.REVIEWS) || [];
  const query = new URLSearchParams(window.location.search);
  const id = Number(query.get("id")) || 3;
  const product = products.find((item) => item.id === id) || products[0];
  if (!product) {
    return;
  }

  const selection = {
    length: product.lengths[0],
    color: product.colors[0],
    texture: product.textures[0],
    laceType: product.laceTypes[0],
    qty: 1
  };

  const baseLength = Number(product.lengths[0].replace('"', ""));
  const PRODUCT_FALLBACK_IMAGE = "assets/images/pr.png";
  const galleryImages = Array.isArray(product.galleryImages) && product.galleryImages.length
    ? product.galleryImages
    : [PRODUCT_FALLBACK_IMAGE, PRODUCT_FALLBACK_IMAGE, PRODUCT_FALLBACK_IMAGE];

  function currentPrice() {
    const selectedLength = Number(selection.length.replace('"', ""));
    return product.price + Math.max(0, selectedLength - baseLength) * 2000;
  }

  function renderStars(value) {
    return Array.from({ length: 5 })
      .map((_, index) => (index < Math.round(value) ? "★" : "☆"))
      .join("");
  }

  function colorMap(name) {
    const map = {
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
    return map[name] || "#666";
  }

  function renderPage() {
    document.title = `${product.name} | Mams Store World`;
    root.innerHTML = `
      <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10 lg:py-16">
        <div class="grid gap-10 lg:grid-cols-2">
          <div>
            <div class="rounded-2xl h-[440px] md:h-[560px] overflow-hidden border border-white/10 bg-black">
              <img data-main-image src="${galleryImages[0]}" alt="${product.name}" class="h-full w-full object-cover object-center transition-all duration-300" />
            </div>
            <div class="grid grid-cols-3 gap-3 mt-4" data-thumbs>
              ${galleryImages
                .slice(0, 3)
                .map(
                  (imageSrc, index) => `
                <button data-thumb="${index}" data-image-src="${imageSrc}" class="rounded-xl h-28 border overflow-hidden ${index === 0 ? "border-[#c9a96e]" : "border-white/15"}">
                  <img src="${imageSrc}" alt="${product.name} ${index + 1}" class="h-full w-full object-cover object-center" />
                </button>
              `
                )
                .join("")}
            </div>
          </div>
          <div class="space-y-6">
            <span class="inline-flex rounded-full border border-[#c9a96e] px-3 py-1 text-[11px] tracking-[0.16em] uppercase text-[#c9a96e]">${product.type === "human_hair" ? "Human Hair" : "Blend Hair"}</span>
            <h1 class="font-display text-4xl lg:text-5xl">${product.name}</h1>
            <div class="flex items-center gap-3">
              <p id="product-price" class="text-2xl font-semibold">${window.MAMS_UTILS.formatPrice(currentPrice())}</p>
              <p class="text-[#c9a96e]">${renderStars(product.rating)}</p>
              <p class="text-sm text-white/70">(${product.reviews} avis)</p>
            </div>
            <p class="text-[#e4dccf] leading-relaxed">${product.description}</p>

            <div class="space-y-4">
              <div>
                <p class="label-caps text-[#c9a96e] mb-2">Longueur</p>
                <div class="flex flex-wrap gap-2" data-length-group>
                  ${product.lengths
                    .map(
                      (length, index) => `
                    <button data-length="${length}" class="h-10 px-4 rounded-full border ${index === 0 ? "bg-[#c9a96e] text-black border-[#c9a96e]" : "border-white/25 text-white"}">${length}</button>
                  `
                    )
                    .join("")}
                </div>
              </div>

              <div>
                <p class="label-caps text-[#c9a96e] mb-2">Couleur</p>
                <div class="flex flex-wrap gap-3" data-color-group>
                  ${product.colors
                    .map(
                      (color, index) => `
                    <button title="${color}" data-color="${color}" class="h-9 w-9 rounded-full border ${index === 0 ? "ring-2 ring-[#c9a96e]" : ""}" style="background:${colorMap(color)}"></button>
                  `
                    )
                    .join("")}
                </div>
              </div>

              <div>
                <p class="label-caps text-[#c9a96e] mb-2">Texture</p>
                <div class="flex flex-wrap gap-2" data-texture-group>
                  ${product.textures
                    .map(
                      (texture, index) => `
                    <button data-texture="${texture}" class="h-10 px-4 rounded-full border ${index === 0 ? "bg-[#c9a96e] text-black border-[#c9a96e]" : "border-white/25 text-white"}">${texture}</button>
                  `
                    )
                    .join("")}
                </div>
              </div>

              <div>
                <p class="label-caps text-[#c9a96e] mb-2">Type</p>
                <div class="flex flex-wrap gap-2" data-lace-group>
                  ${product.laceTypes
                    .map(
                      (type, index) => `
                    <button data-lace="${type}" class="h-10 px-4 rounded-full border ${index === 0 ? "bg-[#c9a96e] text-black border-[#c9a96e]" : "border-white/25 text-white"}">${type}</button>
                  `
                    )
                    .join("")}
                </div>
              </div>
            </div>

            <div class="flex items-center gap-3">
              <button data-qty-minus class="h-10 w-10 rounded-full border border-white/20">−</button>
              <span data-qty-value class="min-w-8 text-center font-semibold">1</span>
              <button data-qty-plus class="h-10 w-10 rounded-full border border-white/20">+</button>
            </div>

            <div class="space-y-3">
              <button data-add-cart class="w-full h-12 rounded-full bg-[#c9a96e] text-black text-sm font-semibold tracking-[0.14em] uppercase hover:brightness-110 transition">
                Ajouter au panier
              </button>
              <a data-whatsapp-order target="_blank" rel="noopener noreferrer" class="w-full h-12 rounded-full bg-[#25D366] text-black text-sm font-semibold tracking-[0.14em] uppercase grid place-items-center hover:brightness-110 transition">
                Commander via WhatsApp
              </a>
            </div>

            <div class="divide-y divide-white/10 border-y border-white/10">
              <div class="accordion-item is-open">
                <button class="w-full py-4 text-left flex justify-between items-center" data-accordion-toggle>Description <span>＋</span></button>
                <div class="accordion-content pb-4 text-sm text-white/75">${product.description}</div>
              </div>
              <div class="accordion-item">
                <button class="w-full py-4 text-left flex justify-between items-center" data-accordion-toggle>Entretien <span>＋</span></button>
                <div class="accordion-content pb-4 text-sm text-white/75">Lavage doux sans sulfates, séchage à l'air libre, rangement sur tête support.</div>
              </div>
              <div class="accordion-item">
                <button class="w-full py-4 text-left flex justify-between items-center" data-accordion-toggle>Livraison & Retours <span>＋</span></button>
                <div class="accordion-content pb-4 text-sm text-white/75">Dakar en 24h, régions 2-3 jours. Retours sous 48h pour produit non porté.</div>
              </div>
            </div>
          </div>
        </div>
      </section>
    `;
  }

  function setActiveInGroup(groupSelector, buttonSelector, activeClassSet) {
    const group = root.querySelector(groupSelector);
    if (!group) {
      return;
    }
    group.querySelectorAll(buttonSelector).forEach((button) => {
      button.classList.remove(...activeClassSet.active);
      button.classList.add(...activeClassSet.inactive);
    });
  }

  function bindSelections() {
    root.querySelectorAll("[data-length]").forEach((button) => {
      button.addEventListener("click", () => {
        setActiveInGroup("[data-length-group]", "[data-length]", {
          active: ["bg-[#c9a96e]", "text-black", "border-[#c9a96e]"],
          inactive: ["border-white/25", "text-white"]
        });
        button.classList.remove("border-white/25", "text-white");
        button.classList.add("bg-[#c9a96e]", "text-black", "border-[#c9a96e]");
        selection.length = button.dataset.length;
        updatePriceAndWhatsApp();
      });
    });

    root.querySelectorAll("[data-color]").forEach((button) => {
      button.addEventListener("click", () => {
        root.querySelectorAll("[data-color]").forEach((dot) => dot.classList.remove("ring-2", "ring-[#c9a96e]"));
        button.classList.add("ring-2", "ring-[#c9a96e]");
        selection.color = button.dataset.color;
        updatePriceAndWhatsApp();
      });
    });

    root.querySelectorAll("[data-texture]").forEach((button) => {
      button.addEventListener("click", () => {
        setActiveInGroup("[data-texture-group]", "[data-texture]", {
          active: ["bg-[#c9a96e]", "text-black", "border-[#c9a96e]"],
          inactive: ["border-white/25", "text-white"]
        });
        button.classList.remove("border-white/25", "text-white");
        button.classList.add("bg-[#c9a96e]", "text-black", "border-[#c9a96e]");
        selection.texture = button.dataset.texture;
        updatePriceAndWhatsApp();
      });
    });

    root.querySelectorAll("[data-lace]").forEach((button) => {
      button.addEventListener("click", () => {
        setActiveInGroup("[data-lace-group]", "[data-lace]", {
          active: ["bg-[#c9a96e]", "text-black", "border-[#c9a96e]"],
          inactive: ["border-white/25", "text-white"]
        });
        button.classList.remove("border-white/25", "text-white");
        button.classList.add("bg-[#c9a96e]", "text-black", "border-[#c9a96e]");
        selection.laceType = button.dataset.lace;
        updatePriceAndWhatsApp();
      });
    });
  }

  function bindGallery() {
    const mainImage = root.querySelector("[data-main-image]");
    root.querySelectorAll("[data-thumb]").forEach((button) => {
      button.addEventListener("click", () => {
        root.querySelectorAll("[data-thumb]").forEach((thumb) => thumb.classList.remove("border-[#c9a96e]"));
        button.classList.add("border-[#c9a96e]");
        mainImage.classList.add("opacity-60");
        setTimeout(() => {
          mainImage.src = button.dataset.imageSrc || PRODUCT_FALLBACK_IMAGE;
          mainImage.classList.remove("opacity-60");
        }, 150);
      });
    });
  }

  function bindQuantity() {
    const qtyNode = root.querySelector("[data-qty-value]");
    root.querySelector("[data-qty-minus]").addEventListener("click", () => {
      selection.qty = Math.max(1, selection.qty - 1);
      qtyNode.textContent = selection.qty;
      updatePriceAndWhatsApp();
    });
    root.querySelector("[data-qty-plus]").addEventListener("click", () => {
      selection.qty += 1;
      qtyNode.textContent = selection.qty;
      updatePriceAndWhatsApp();
    });
  }

  function updatePriceAndWhatsApp() {
    const computedPrice = currentPrice();
    const fullPrice = computedPrice * selection.qty;
    root.querySelector("#product-price").textContent = window.MAMS_UTILS.formatPrice(computedPrice);
    const message = encodeURIComponent(
      `Bonjour Mams Store World, je souhaite commander ${product.name}.\nLongueur: ${selection.length}\nCouleur: ${selection.color}\nTexture: ${selection.texture}\nType: ${selection.laceType}\nQuantité: ${selection.qty}\nTotal: ${window.MAMS_UTILS.formatPrice(fullPrice)}`
    );
    root.querySelector("[data-whatsapp-order]").href = `${window.MAMS_UTILS.whatsappUrl}?text=${message}`;
  }

  function bindAddToCart() {
    root.querySelector("[data-add-cart]").addEventListener("click", () => {
      window.MamsCart.addToCart(product, {
        length: selection.length,
        color: selection.color,
        texture: selection.texture,
        laceType: selection.laceType
      }, selection.qty);
    });
  }

  function bindAccordions() {
    root.querySelectorAll("[data-accordion-toggle]").forEach((button) => {
      button.addEventListener("click", () => {
        const item = button.closest(".accordion-item");
        item.classList.toggle("is-open");
      });
    });
  }

  function renderReviewsAndRelated() {
    const scopedReviews = reviews.filter((entry) => entry.productId === product.id).slice(0, 4);
    const reviewHost = document.querySelector("[data-product-reviews]");
    const relatedHost = document.querySelector("[data-related-products]");
    if (reviewHost) {
      reviewHost.innerHTML = `
        <div class="grid gap-6 lg:grid-cols-2">
          <div class="rounded-2xl border border-white/10 bg-[#111] p-6">
            <p class="font-display text-4xl">${product.rating.toFixed(1)}/5</p>
            <p class="text-[#c9a96e] mt-1">${renderStars(product.rating)}</p>
            <div class="mt-6 space-y-3">
              ${[5, 4, 3, 2, 1]
                .map((star) => {
                  const width = star === 5 ? 78 : star === 4 ? 18 : 4;
                  return `<div class="flex items-center gap-3"><span class="text-xs w-4">${star}</span><div class="h-2 flex-1 bg-white/10 rounded-full overflow-hidden"><span class="block h-full bg-[#c9a96e]" style="width:${width}%"></span></div></div>`;
                })
                .join("")}
            </div>
          </div>
          <div class="space-y-4">
            ${scopedReviews
              .map(
                (entry) => `
              <article class="rounded-2xl border border-white/10 bg-[#111] p-5">
                <div class="flex items-center justify-between">
                  <p class="font-semibold">${entry.firstName}</p>
                  <p class="text-xs text-white/60">${entry.date}</p>
                </div>
                <p class="text-[#c9a96e] mt-1">${renderStars(entry.rating)}</p>
                <p class="mt-2 text-sm text-white/75">${entry.text}</p>
              </article>
            `
              )
              .join("")}
          </div>
        </div>
        <div class="mt-8">
          <h3 class="font-display text-2xl mb-4">Photos des clientes</h3>
          <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
            ${Array.from({ length: 6 })
              .map(() => `<div class="placeholder-media rounded-xl h-32"></div>`)
              .join("")}
          </div>
        </div>
      `;
    }

    if (relatedHost) {
      relatedHost.innerHTML = products
        .filter((item) => item.id !== product.id)
        .slice(0, 4)
        .map(
          (item) => `
        <article class="rounded-2xl border border-white/10 bg-[#111] p-3 hover-lift">
          <a href="product.html?id=${item.id}" class="block rounded-xl h-56 overflow-hidden border border-white/10 bg-black">
            <img src="${PRODUCT_FALLBACK_IMAGE}" alt="${item.name}" class="h-full w-full object-cover object-center" />
          </a>
          <div class="pt-3">
            <a href="product.html?id=${item.id}" class="font-display text-2xl hover:text-[#c9a96e] transition">${item.name}</a>
            <p class="text-sm text-white/70 mt-1">${window.MAMS_UTILS.formatPrice(item.price)}</p>
          </div>
        </article>
      `
        )
        .join("");
    }
  }

  renderPage();
  bindGallery();
  bindSelections();
  bindQuantity();
  bindAddToCart();
  bindAccordions();
  renderReviewsAndRelated();
  updatePriceAndWhatsApp();
})();
