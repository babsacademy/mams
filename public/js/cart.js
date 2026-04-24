document.addEventListener('alpine:init', () => {
    const storageKey = 'mams_storefront_cart';
    const legacyStorageKey = 'prosmax_cart';

    const readItems = () => {
        const raw = localStorage.getItem(storageKey) ?? localStorage.getItem(legacyStorageKey);

        try {
            const parsed = raw ? JSON.parse(raw) : [];

            return Array.isArray(parsed) ? parsed : [];
        } catch (error) {
            return [];
        }
    };

    const writeItems = (items) => {
        localStorage.setItem(storageKey, JSON.stringify(items));
        localStorage.removeItem(legacyStorageKey);
    };

    Alpine.store('toast', {
        visible: false,
        message: '',
        productName: '',
        _timeout: null,

        show(product) {
            if (this._timeout) {
                clearTimeout(this._timeout);
            }

            this.productName = product.name || 'Produit';
            this.message = 'Ajoute au panier';
            this.visible = true;
            this._timeout = setTimeout(() => {
                this.visible = false;
            }, 2200);
        },

        hide() {
            this.visible = false;

            if (this._timeout) {
                clearTimeout(this._timeout);
            }
        },
    });

    Alpine.store('cart', {
        items: readItems(),
        isOpen: false,

        add(product) {
            const existing = this.items.find((item) =>
                item.id === product.id &&
                item.size === product.size &&
                item.color === product.color,
            );

            if (existing) {
                existing.quantity += product.quantity || 1;
            } else {
                this.items.push({ ...product, quantity: product.quantity || 1 });
            }

            this.save();
            Alpine.store('toast').show(product);
        },

        remove(productId, size, color) {
            this.items = this.items.filter((item) => {
                return ! (item.id === productId && item.size === size && item.color === color);
            });

            this.save();
        },

        updateQuantity(productId, size, color, change) {
            const item = this.items.find((entry) => {
                return entry.id === productId && entry.size === size && entry.color === color;
            });

            if (! item) {
                return;
            }

            item.quantity += change;

            if (item.quantity <= 0) {
                this.remove(productId, size, color);

                return;
            }

            this.save();
        },

        get total() {
            return this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        },

        get count() {
            return this.items.reduce((sum, item) => sum + item.quantity, 0);
        },

        save() {
            writeItems(this.items);
        },

        open() {
            this.isOpen = true;
            document.body.style.overflow = 'hidden';
        },

        close() {
            this.isOpen = false;
            document.body.style.overflow = '';
        },

        clear() {
            this.items = [];
            this.save();
        },
    });
});
