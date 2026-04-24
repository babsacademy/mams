@extends('layouts.shop')

@section('title', 'Contact | ' . ($siteInfo['shop_name'] ?? 'Mams Store World'))
@section('description', 'Contactez la boutique depuis le nouveau front Mams.')

@php
    $phone = $siteInfo['phone_primary'] ?? '';
    $whatsapp = $siteInfo['whatsapp_number'] ?? '221771831987';
    $waNumber = ltrim(preg_replace('/[^\d]/', '', $whatsapp), '+');
    $waLink = 'https://wa.me/' . $waNumber;
    $address = $siteInfo['physical_address'] ?? 'Dakar, Senegal';
    $email = $siteInfo['contact_email'] ?? '';
    $instagram = $siteInfo['instagram_url'] ?? '';
    $tiktok = $siteInfo['tiktok_url'] ?? '';
@endphp

@section('content')
    <div x-data="contactForm()" class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <section class="pb-12 text-center">
            <p class="label-caps text-[#c9a96e]">Nous joindre</p>
            <h1 class="mt-3 font-display text-5xl">Contactez-nous</h1>
            <p class="mx-auto mt-4 max-w-2xl text-white/70">Le formulaire de contact garde la logique Laravel actuelle, avec un habillage complet dans le nouveau theme storefront.</p>
        </section>

        <div class="grid gap-12 lg:grid-cols-5">
            <div class="space-y-6 lg:col-span-2">
                <div class="rounded-2xl border border-white/10 bg-[#111] p-6">
                    <h2 class="mb-6 font-display text-3xl">Coordonnees</h2>
                    <div class="space-y-5 text-sm text-white/75">
                        <a href="{{ $waLink }}" target="_blank" rel="noopener noreferrer" class="block rounded-xl border border-white/10 p-4 transition hover:border-[#25D366]">
                            <p class="label-caps text-[#c9a96e]">WhatsApp</p>
                            <p class="mt-2">{{ $whatsapp }}</p>
                        </a>

                        @if ($phone)
                            <a href="tel:{{ $phone }}" class="block rounded-xl border border-white/10 p-4 transition hover:border-[#c9a96e]">
                                <p class="label-caps text-[#c9a96e]">Telephone</p>
                                <p class="mt-2">{{ $phone }}</p>
                            </a>
                        @endif

                        <div class="rounded-xl border border-white/10 p-4">
                            <p class="label-caps text-[#c9a96e]">Adresse</p>
                            <p class="mt-2">{{ $address }}</p>
                        </div>

                        @if ($email)
                            <a href="mailto:{{ $email }}" class="block rounded-xl border border-white/10 p-4 transition hover:border-[#c9a96e]">
                                <p class="label-caps text-[#c9a96e]">Email</p>
                                <p class="mt-2">{{ $email }}</p>
                            </a>
                        @endif
                    </div>
                </div>

                @if ($instagram || $tiktok)
                    <div class="rounded-2xl border border-white/10 bg-[#111] p-6">
                        <h2 class="mb-4 font-display text-3xl">Reseaux</h2>
                        <div class="flex flex-wrap gap-3">
                            @if ($instagram)
                                <a href="{{ $instagram }}" target="_blank" rel="noopener noreferrer" class="inline-flex h-11 items-center rounded-full border border-white/15 px-5 text-xs uppercase tracking-[0.14em] text-white/75 transition hover:border-[#c9a96e] hover:text-white">Instagram</a>
                            @endif
                            @if ($tiktok)
                                <a href="{{ $tiktok }}" target="_blank" rel="noopener noreferrer" class="inline-flex h-11 items-center rounded-full border border-white/15 px-5 text-xs uppercase tracking-[0.14em] text-white/75 transition hover:border-[#c9a96e] hover:text-white">TikTok</a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <div class="lg:col-span-3">
                <div class="rounded-2xl border border-white/10 bg-[#111] p-6 sm:p-8">
                    <template x-if="formSent">
                        <div class="py-12 text-center">
                            <div class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-[#c9a96e] text-black">
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <h2 class="mt-6 font-display text-4xl">Message envoye</h2>
                            <p class="mx-auto mt-3 max-w-md text-white/65">Votre message a bien ete transmis. Nous reviendrons vers vous rapidement.</p>
                            <button type="button" class="mt-6 inline-flex h-11 items-center rounded-full bg-[#c9a96e] px-6 text-xs font-semibold uppercase tracking-[0.14em] text-black" @click="formSent = false; resetForm()">
                                Envoyer un autre message
                            </button>
                        </div>
                    </template>

                    <template x-if="!formSent">
                        <form class="space-y-5" @submit.prevent="submitForm()">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <input type="text" x-model="form.name" placeholder="Nom complet" class="h-12 w-full rounded-xl border border-white/15 bg-transparent px-4">
                                    <p x-show="errors.name" x-text="errors.name" class="mt-2 text-xs text-red-300" x-cloak></p>
                                </div>
                                <div>
                                    <input type="email" x-model="form.email" placeholder="Email" class="h-12 w-full rounded-xl border border-white/15 bg-transparent px-4">
                                    <p x-show="errors.email" x-text="errors.email" class="mt-2 text-xs text-red-300" x-cloak></p>
                                </div>
                            </div>

                            <div>
                                <select x-model="form.subject" class="h-12 w-full rounded-xl border border-white/15 bg-transparent px-4">
                                    <option value="" class="text-black">Choisir un sujet</option>
                                    <option value="commande" class="text-black">Question commande</option>
                                    <option value="produit" class="text-black">Question produit</option>
                                    <option value="livraison" class="text-black">Livraison</option>
                                    <option value="autre" class="text-black">Autre</option>
                                </select>
                                <p x-show="errors.subject" x-text="errors.subject" class="mt-2 text-xs text-red-300" x-cloak></p>
                            </div>

                            <div class="hidden" aria-hidden="true">
                                <input type="text" x-model="form.honeypot" tabindex="-1" autocomplete="off">
                            </div>

                            <div>
                                <textarea rows="6" x-model="form.message" placeholder="Votre message" class="w-full rounded-xl border border-white/15 bg-transparent p-4"></textarea>
                                <p x-show="errors.message" x-text="errors.message" class="mt-2 text-xs text-red-300" x-cloak></p>
                            </div>

                            <button type="submit" class="grid h-12 w-full place-items-center rounded-full bg-[#c9a96e] text-xs font-semibold uppercase tracking-[0.14em] text-black disabled:opacity-60" :disabled="sending">
                                <span x-show="!sending">Envoyer le message</span>
                                <span x-show="sending" x-cloak>Envoi...</span>
                            </button>
                        </form>
                    </template>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function contactForm() {
            return {
                formSent: false,
                sending: false,
                form: {
                    name: '',
                    email: '',
                    subject: '',
                    message: '',
                    honeypot: '',
                },
                errors: {},

                validate() {
                    const errors = {};

                    if (! this.form.name.trim()) {
                        errors.name = 'Le nom est requis';
                    }

                    if (! this.form.email.trim()) {
                        errors.email = 'L email est requis';
                    } else if (! /\S+@\S+\.\S+/.test(this.form.email)) {
                        errors.email = 'Email invalide';
                    }

                    if (! this.form.subject) {
                        errors.subject = 'Le sujet est requis';
                    }

                    if (! this.form.message.trim()) {
                        errors.message = 'Le message est requis';
                    } else if (this.form.message.trim().length < 10) {
                        errors.message = 'Le message doit contenir au moins 10 caracteres';
                    }

                    this.errors = errors;

                    return Object.keys(errors).length === 0;
                },

                async submitForm() {
                    if (! this.validate()) {
                        return;
                    }

                    this.sending = true;

                    try {
                        const response = await fetch('{{ route('contact.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify(this.form),
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            this.formSent = true;
                        } else if (response.status === 422 && data.errors) {
                            Object.entries(data.errors).forEach(([key, value]) => {
                                this.errors[key] = Array.isArray(value) ? value[0] : value;
                            });
                        }
                    } finally {
                        this.sending = false;
                    }
                },

                resetForm() {
                    this.form = {
                        name: '',
                        email: '',
                        subject: '',
                        message: '',
                        honeypot: '',
                    };
                    this.errors = {};
                },
            };
        }
    </script>
@endpush
