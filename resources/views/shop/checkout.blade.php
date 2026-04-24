@extends('layouts.shop')

@section('title', 'Checkout | ' . ($siteInfo['shop_name'] ?? 'Mams Store World'))
@section('description', 'Finalisez votre commande dans le nouveau checkout Mams.')

@section('content')
    <div x-data="checkoutData()" x-init="if ($store.cart.items.length === 0) { window.location.href = '{{ route('panier') }}'; }" class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-wrap gap-2">
            <span class="inline-flex h-9 items-center rounded-full bg-[#c9a96e] px-4 text-xs uppercase tracking-[0.14em] text-black">1. Infos</span>
            <span class="inline-flex h-9 items-center rounded-full border border-white/20 px-4 text-xs uppercase tracking-[0.14em]">2. Livraison</span>
            <span class="inline-flex h-9 items-center rounded-full border border-white/20 px-4 text-xs uppercase tracking-[0.14em]">3. Paiement</span>
        </div>

        <div class="grid gap-8 lg:grid-cols-[1fr_360px]">
            <form class="space-y-8" @submit.prevent="submitCheckout()">
                <template x-if="globalError">
                    <div class="rounded-2xl border border-red-400/30 bg-red-950/40 px-5 py-4 text-sm text-red-100" x-text="globalError"></div>
                </template>

                <section class="rounded-2xl border border-white/10 bg-[#111] p-6">
                    <h1 class="mb-5 font-display text-4xl">Informations client</h1>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <input type="text" x-model="form.firstname" placeholder="Prenom" class="h-12 w-full rounded-xl border border-white/15 bg-transparent px-4">
                            <p x-show="errors.firstname" x-text="errors.firstname" class="mt-2 text-xs text-red-300" x-cloak></p>
                        </div>
                        <div>
                            <input type="text" x-model="form.lastname" placeholder="Nom" class="h-12 w-full rounded-xl border border-white/15 bg-transparent px-4">
                            <p x-show="errors.lastname" x-text="errors.lastname" class="mt-2 text-xs text-red-300" x-cloak></p>
                        </div>
                        <div class="md:col-span-2">
                            <input type="email" x-model="form.email" placeholder="Email (optionnel)" class="h-12 w-full rounded-xl border border-white/15 bg-transparent px-4">
                        </div>
                        <div class="md:col-span-2">
                            <input type="tel" x-model="form.phone" placeholder="Telephone / WhatsApp" class="h-12 w-full rounded-xl border border-white/15 bg-transparent px-4">
                            <p x-show="errors.phone" x-text="errors.phone" class="mt-2 text-xs text-red-300" x-cloak></p>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-white/10 bg-[#111] p-6">
                    <h2 class="mb-4 font-display text-3xl">Zone de livraison</h2>
                    <div class="space-y-3">
                        <template x-for="zone in deliveryZones" :key="zone.id">
                            <label class="block cursor-pointer rounded-xl border border-white/15 p-4 transition hover:border-[#c9a96e]" :class="String(form.zone_id) === String(zone.id) ? 'border-[#c9a96e] bg-white/5' : ''">
                                <input type="radio" name="shipping" class="mr-2" :value="zone.id" x-model="form.zone_id" @change="updateDeliveryFee()">
                                <span x-text="zone.name + ' - ' + new Intl.NumberFormat('fr-FR').format(zone.price) + ' FCFA'"></span>
                            </label>
                        </template>
                        <p x-show="errors.zone_id" x-text="errors.zone_id" class="text-xs text-red-300" x-cloak></p>

                        <textarea rows="4" x-model="form.address" placeholder="Adresse complete" class="w-full rounded-xl border border-white/15 bg-transparent p-4"></textarea>
                        <p x-show="errors.address" x-text="errors.address" class="text-xs text-red-300" x-cloak></p>

                        <input type="text" x-model="form.landmark" placeholder="Repere ou note de livraison" class="h-12 w-full rounded-xl border border-white/15 bg-transparent px-4">
                    </div>
                </section>

                <section class="rounded-2xl border border-white/10 bg-[#111] p-6">
                    <h2 class="mb-4 font-display text-3xl">Mode de paiement</h2>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="cursor-pointer rounded-xl border border-white/15 p-4" :class="form.payment_method === 'wave' ? 'border-[#c9a96e] bg-white/5' : ''">
                            <input type="radio" value="wave" x-model="form.payment_method" class="mr-2"> Wave
                        </label>
                        <label class="cursor-pointer rounded-xl border border-white/15 p-4" :class="form.payment_method === 'cash' ? 'border-[#c9a96e] bg-white/5' : ''">
                            <input type="radio" value="cash" x-model="form.payment_method" class="mr-2"> Paiement a la livraison
                        </label>
                    </div>
                    <p x-show="errors.payment_method" x-text="errors.payment_method" class="mt-2 text-xs text-red-300" x-cloak></p>
                </section>

                <section class="rounded-2xl border border-white/10 bg-[#111] p-6">
                    <h2 class="mb-4 font-display text-3xl">Notes</h2>
                    <textarea rows="4" x-model="form.notes" placeholder="Instructions supplementaires pour la commande" class="w-full rounded-xl border border-white/15 bg-transparent p-4"></textarea>
                </section>

                <button type="submit" class="grid h-12 w-full place-items-center rounded-full bg-[#c9a96e] text-xs font-semibold uppercase tracking-[0.14em] text-black disabled:opacity-60" :disabled="submitting">
                    <span x-show="!submitting">Confirmer la commande</span>
                    <span x-show="submitting" x-cloak>Traitement...</span>
                </button>
            </form>

            <aside class="h-fit rounded-2xl border border-white/10 bg-[#111] p-6 lg:sticky lg:top-24">
                <h2 class="font-display text-3xl">Recapitulatif</h2>
                <div class="mt-5 space-y-3">
                    <template x-if="$store.cart.items.length === 0">
                        <p class="text-sm text-white/70">Votre panier est vide.</p>
                    </template>
                    <template x-for="item in $store.cart.items" :key="item.id + '-' + item.size + '-' + item.color">
                        <div class="flex items-center justify-between text-sm">
                            <span x-text="item.name + ' x ' + item.quantity"></span>
                            <span x-text="new Intl.NumberFormat('fr-FR').format(item.price * item.quantity) + ' FCFA'"></span>
                        </div>
                    </template>
                </div>

                <div class="mt-4 space-y-2 border-t border-white/10 pt-4 text-sm">
                    <div class="flex justify-between"><span>Sous-total</span><span x-text="new Intl.NumberFormat('fr-FR').format($store.cart.total) + ' FCFA'"></span></div>
                    <div class="flex justify-between"><span>Livraison</span><span x-text="deliveryFee > 0 ? new Intl.NumberFormat('fr-FR').format(deliveryFee) + ' FCFA' : 'A definir'"></span></div>
                    <div class="flex justify-between text-lg font-semibold"><span>Total</span><span x-text="new Intl.NumberFormat('fr-FR').format($store.cart.total + deliveryFee) + ' FCFA'"></span></div>
                </div>
            </aside>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const deliveryZones = @json($zones->map(fn ($zone) => ['id' => $zone->id, 'name' => $zone->name, 'price' => $zone->delivery_fee]));

        function checkoutData() {
            return {
                deliveryZones,
                deliveryFee: 0,
                submitting: false,
                globalError: '',

                form: {
                    firstname: '',
                    lastname: '',
                    phone: '',
                    email: '',
                    zone_id: '',
                    address: '',
                    landmark: '',
                    payment_method: 'wave',
                    notes: '',
                },

                errors: {},

                updateDeliveryFee() {
                    const zone = this.deliveryZones.find((entry) => String(entry.id) === String(this.form.zone_id));
                    this.deliveryFee = zone ? Number(zone.price) : 0;
                },

                validate() {
                    const errors = {};

                    if (! this.form.firstname.trim()) {
                        errors.firstname = 'Le prenom est requis';
                    }

                    if (! this.form.lastname.trim()) {
                        errors.lastname = 'Le nom est requis';
                    }

                    if (! this.form.phone.trim()) {
                        errors.phone = 'Le telephone est requis';
                    }

                    if (! this.form.zone_id) {
                        errors.zone_id = 'Choisissez une zone de livraison';
                    }

                    if (! this.form.address.trim()) {
                        errors.address = 'L adresse est requise';
                    }

                    if (! this.form.payment_method) {
                        errors.payment_method = 'Choisissez un mode de paiement';
                    }

                    this.errors = errors;

                    return Object.keys(errors).length === 0;
                },

                async submitCheckout() {
                    this.globalError = '';

                    if (! this.validate()) {
                        return;
                    }

                    const items = Alpine.store('cart').items;

                    if (! items.length) {
                        this.globalError = 'Votre panier est vide.';
                        return;
                    }

                    this.submitting = true;

                    try {
                        const response = await fetch('{{ route('checkout.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                customer: {
                                    first_name: this.form.firstname.trim(),
                                    last_name: this.form.lastname.trim(),
                                    phone: this.form.phone.trim(),
                                    email: this.form.email.trim() || null,
                                },
                                delivery: {
                                    zone_id: this.form.zone_id,
                                    address: this.form.address.trim(),
                                    notes: [this.form.landmark.trim(), this.form.notes.trim()].filter(Boolean).join(' - ') || null,
                                },
                                payment: {
                                    method: this.form.payment_method,
                                },
                                items: items.map((item) => ({
                                    product_id: item.id,
                                    quantity: item.quantity,
                                })),
                            }),
                        });

                        const data = await response.json();

                        if (response.ok && data.redirect_url) {
                            Alpine.store('cart').clear();
                            window.location.href = data.redirect_url;
                            return;
                        }

                        if (response.status === 422 && data.errors) {
                            const mappedErrors = {};
                            const keyMap = {
                                'customer.first_name': 'firstname',
                                'customer.last_name': 'lastname',
                                'customer.phone': 'phone',
                                'delivery.zone_id': 'zone_id',
                                'delivery.address': 'address',
                                'payment.method': 'payment_method',
                            };

                            Object.entries(data.errors).forEach(([key, value]) => {
                                mappedErrors[keyMap[key] ?? key] = Array.isArray(value) ? value[0] : value;
                            });

                            this.errors = { ...this.errors, ...mappedErrors };
                        } else {
                            this.globalError = data.message || 'Une erreur est survenue pendant la commande.';
                        }
                    } catch (error) {
                        this.globalError = 'Impossible d envoyer la commande pour le moment.';
                    } finally {
                        this.submitting = false;
                    }
                },
            };
        }
    </script>
@endpush
