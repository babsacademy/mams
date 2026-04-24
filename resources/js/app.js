/**
 * Sacoche Chic – Script Principal (Vite build)
 * Header/footer gérés par Blade — pas de Components.inject.
 * App exposé globalement pour les onclick inline dans les templates.
 */

document.addEventListener('DOMContentLoaded', () => App.init());

const App = {
    init() {
        this.HeaderScroll.init();
        this.MobileMenu.init();
        this.Cart.init();
        this.ScrollReveal.init();
        this.Accordion.init();
        this.FormValidation.init();
    },

    HeaderScroll: {
        init() {
            const header = document.querySelector('header');
            if (!header) { return; }
            window.addEventListener('scroll', () => {
                header.classList.toggle('shadow-md', window.scrollY > 60);
            }, { passive: true });
        },
    },

    MobileMenu: {
        init() {
            const burger     = document.getElementById('burger-menu');
            const mobileMenu = document.getElementById('mobile-menu');
            const closeBtn   = document.getElementById('close-menu');
            if (!burger || !mobileMenu) { return; }

            const open  = () => { mobileMenu.classList.remove('translate-x-full'); mobileMenu.removeAttribute('aria-hidden'); document.body.classList.add('overflow-hidden'); };
            const close = () => { mobileMenu.classList.add('translate-x-full'); mobileMenu.setAttribute('aria-hidden', 'true'); document.body.classList.remove('overflow-hidden'); };

            burger.addEventListener('click', open);
            if (closeBtn) { closeBtn.addEventListener('click', close); }
            mobileMenu.querySelectorAll('a').forEach(link => link.addEventListener('click', close));
        },
    },

    Cart: {
        storageKey: 'sacoche_chic_cart',

        init() {
            this.updateBadge();
            this.renderCartPage();
            this.setupAddToCartButtons();
        },

        getItems() {
            try { return JSON.parse(localStorage.getItem(this.storageKey)) || []; } catch { return []; }
        },

        saveCart(items) { localStorage.setItem(this.storageKey, JSON.stringify(items)); },

        clearCart() { localStorage.removeItem(this.storageKey); this.updateBadge(); },

        saveOrder(orderData) {
            const orders = JSON.parse(localStorage.getItem('sacoche_chic_orders') || '[]');
            orders.unshift(orderData);
            localStorage.setItem('sacoche_chic_orders', JSON.stringify(orders));
        },

        getOrders() { return JSON.parse(localStorage.getItem('sacoche_chic_orders') || '[]'); },

        addItem(product) {
            const items    = this.getItems();
            const existing = items.find(i => i.id === product.id && i.variant === product.variant);
            if (existing) { existing.quantity += product.quantity; } else { items.push(product); }
            this.saveCart(items);
            this.updateBadge();
            document.querySelectorAll('.cart-badge').forEach(b => { b.classList.add('scale-125'); setTimeout(() => b.classList.remove('scale-125'), 300); });
            App.Toast.show('Article ajouté au panier ✓');
        },

        removeItem(id, variant) {
            this.saveCart(this.getItems().filter(i => !(i.id === id && i.variant === variant)));
            this.updateBadge();
            this.renderCartPage();
        },

        updateQuantity(id, variant, delta) {
            const items = this.getItems();
            const item  = items.find(i => i.id === id && i.variant === variant);
            if (!item) { return; }
            item.quantity += delta;
            if (item.quantity <= 0) { this.removeItem(id, variant); return; }
            this.saveCart(items);
            this.updateBadge();
            this.renderCartPage();
        },

        updateBadge() {
            const count = this.getItems().reduce((acc, i) => acc + i.quantity, 0);
            document.querySelectorAll('.cart-badge').forEach(b => { b.textContent = count; b.classList.toggle('hidden', count === 0); });
        },

        setupAddToCartButtons() {
            document.querySelectorAll('.add-to-cart').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id       = btn.dataset.id;
                    const name     = btn.dataset.name;
                    const price    = parseFloat(btn.dataset.price);
                    const image    = btn.dataset.image || '';
                    const variant  = document.querySelector('input[name="variant"]:checked')?.value || 'Unique';
                    const qtyEl    = document.getElementById('quantity');
                    const quantity = qtyEl ? Math.max(1, parseInt(qtyEl.value) || 1) : 1;
                    if (!id || !name || isNaN(price)) { return; }
                    this.addItem({ id, name, price, image, variant, quantity });
                });
            });
        },

        renderCartPage() {
            const container = document.getElementById('cart-items-container');
            if (!container) { return; }
            const items = this.getItems();

            if (items.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-24 bg-gray-50 rounded-sm">
                        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-white shadow-sm mb-8 text-gray-300">
                            <svg class="w-9 h-9" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        </div>
                        <p class="text-gray-400 mb-2 uppercase tracking-widest text-sm font-bold">Votre panier est vide</p>
                        <p class="text-gray-400 text-xs mb-10 font-medium">Découvrez notre sélection de pièces exclusives</p>
                        <a href="/boutique" class="inline-block bg-brand-black text-white px-10 py-5 uppercase text-xs tracking-widest font-black transition-all hover:bg-brand-pink rounded-sm shadow-md">Découvrir nos créations</a>
                    </div>`;
                document.getElementById('cart-summary')?.classList.add('hidden');
                return;
            }

            container.innerHTML = items.map(item => `
                <div class="grid grid-cols-1 md:grid-cols-6 gap-6 items-center py-10 hover:bg-gray-50 transition-colors px-4 rounded-sm">
                    <div class="md:col-span-3 flex items-center space-x-8">
                        <div class="w-32 aspect-[4/5] overflow-hidden rounded-sm shadow-sm flex-shrink-0">
                            <img src="${item.image}" alt="${item.name}" class="w-full h-full object-cover">
                        </div>
                        <div class="space-y-2">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-brand-black">
                                <a href="/produit" class="hover:text-brand-pink transition-colors">${item.name}</a>
                            </h3>
                            <p class="text-xs text-gray-500 uppercase font-semibold tracking-widest">Coloris : ${item.variant}</p>
                        </div>
                    </div>
                    <div class="text-sm font-black text-gray-400 hidden md:block text-center">${item.price.toLocaleString()} FCFA</div>
                    <div class="flex justify-start md:justify-center">
                        <div class="flex items-center border border-gray-200 bg-white rounded-sm overflow-hidden shadow-sm">
                            <button onclick="App.Cart.updateQuantity('${item.id}','${item.variant}',-1)" class="w-10 h-10 flex items-center justify-center hover:bg-gray-100 font-bold transition-colors" aria-label="Diminuer">-</button>
                            <span class="w-12 h-10 flex items-center justify-center text-sm font-black text-brand-black border-x border-gray-100">${item.quantity}</span>
                            <button onclick="App.Cart.updateQuantity('${item.id}','${item.variant}',1)" class="w-10 h-10 flex items-center justify-center hover:bg-gray-100 font-bold transition-colors" aria-label="Augmenter">+</button>
                        </div>
                    </div>
                    <div class="md:text-right flex justify-between md:flex-col items-center md:items-end">
                        <p class="text-base font-black text-brand-black">${(item.price * item.quantity).toLocaleString()} FCFA</p>
                        <button onclick="App.Cart.removeItem('${item.id}','${item.variant}')"
                            class="text-xs uppercase tracking-widest text-gray-400 hover:text-brand-pink font-bold flex items-center mt-4 md:mt-2 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Supprimer
                        </button>
                    </div>
                </div>`).join('');

            this.updateSummary();
        },

        updateSummary() {
            const subtotalEl = document.getElementById('cart-subtotal');
            const totalEl    = document.getElementById('cart-total');
            if (!subtotalEl || !totalEl) { return; }
            const total = this.getItems().reduce((acc, i) => acc + i.price * i.quantity, 0);
            subtotalEl.textContent = `${total.toLocaleString()} FCFA`;
            totalEl.textContent    = `${total.toLocaleString()} FCFA`;
            document.getElementById('cart-summary')?.classList.remove('hidden');
        },
    },

    Toast: {
        show(message) {
            document.querySelectorAll('.sc-toast').forEach(t => t.remove());
            const toast = document.createElement('div');
            toast.className = 'sc-toast fixed bottom-8 left-1/2 -translate-x-1/2 px-8 py-4 bg-brand-black text-white text-xs uppercase tracking-[0.15em] font-bold shadow-2xl z-[999] transition-all duration-400 transform translate-y-20 opacity-0 border-l-4 border-brand-pink';
            toast.textContent = message;
            document.body.appendChild(toast);
            requestAnimationFrame(() => toast.classList.remove('translate-y-20', 'opacity-0'));
            setTimeout(() => { toast.classList.add('translate-y-20', 'opacity-0'); setTimeout(() => toast.remove(), 400); }, 3000);
        },
    },

    ScrollReveal: {
        init() {
            const observer = new IntersectionObserver(entries => {
                entries.forEach(entry => { if (entry.isIntersecting) { entry.target.classList.add('reveal-visible'); observer.unobserve(entry.target); } });
            }, { threshold: 0.08 });
            document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
        },
    },

    Accordion: {
        init() {
            document.querySelectorAll('.accordion-trigger').forEach(trigger => {
                trigger.addEventListener('click', () => {
                    const content = trigger.nextElementSibling;
                    const icon    = trigger.querySelector('.accordion-icon');
                    if (!content) { return; }
                    const isOpen = !content.classList.contains('hidden');
                    content.classList.toggle('hidden', isOpen);
                    if (icon) { icon.classList.toggle('rotate-180', !isOpen); }
                });
            });
        },
    },

    FormValidation: {
        init() {
            document.querySelectorAll('form[data-validate]').forEach(form => {
                form.addEventListener('submit', e => {
                    e.preventDefault();
                    if (this.validate(form) && form.id === 'contact-form') {
                        App.Toast.show('Message envoyé avec succès !');
                        form.reset();
                    }
                });
            });
        },

        validate(form) {
            let valid = true;
            form.querySelectorAll('input[required], textarea[required], select[required]').forEach(input => {
                const val = input.value.trim();
                if (!val) {
                    this.showError(input, 'Ce champ est requis'); valid = false;
                } else if (input.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
                    this.showError(input, 'Email invalide'); valid = false;
                } else if (input.type === 'tel' && val.replace(/\s/g, '').length < 8) {
                    this.showError(input, 'Numéro invalide'); valid = false;
                } else {
                    this.clearError(input);
                }
            });
            return valid;
        },

        showError(input, msg) {
            input.classList.add('!border-red-400');
            let err = input.parentElement.querySelector('.error-msg');
            if (!err) { err = document.createElement('p'); err.className = 'error-msg text-red-500 text-[10px] mt-1 uppercase tracking-wider'; input.parentElement.appendChild(err); }
            err.textContent = msg;
        },

        clearError(input) { input.classList.remove('!border-red-400'); input.parentElement.querySelector('.error-msg')?.remove(); },
    },
};

window.App = App;
