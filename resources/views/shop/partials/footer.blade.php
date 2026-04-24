@php
    $brandName = $siteInfo['shop_name'] ?? 'Mams Store World';
    $phone = $siteInfo['phone_primary'] ?? '';
    $whatsapp = $siteInfo['whatsapp_number'] ?? '221771831987';
    $waNumber = ltrim(preg_replace('/[^\d]/', '', $whatsapp), '+');
    $waLink = 'https://wa.me/' . $waNumber;
    $instagram = $siteInfo['instagram_url'] ?? '';
    $tiktok = $siteInfo['tiktok_url'] ?? '';
    $address = $siteInfo['physical_address'] ?? 'Dakar, Senegal';
    $email = $siteInfo['contact_email'] ?? '';
    $footerLogoUrl = $siteInfo['footer_logo_url'] ?? $siteInfo['logo_url'] ?? null;
@endphp

<footer class="mt-20 border-t border-white/10 bg-black">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid gap-10 md:grid-cols-3">
            <div>
                @if ($footerLogoUrl)
                    <div class="inline-flex h-14 w-[180px] items-center justify-center sm:h-16 sm:w-[230px]">
                        <img src="{{ $footerLogoUrl }}" alt="{{ $brandName }}" class="h-full w-full object-contain object-center">
                    </div>
                @else
                    <p class="font-display text-4xl text-[#c9a96e]">{{ $brandName }}</p>
                @endif
                <p class="mt-5 max-w-sm text-sm leading-7 text-[#d8d1c4]">
                    Une vitrine premium pour la beaute, le style et l'experience client. Chaque page a ete adaptee pour une presentation plus editoriale et plus luxe.
                </p>
            </div>

            <div>
                <h3 class="label-caps mb-4 text-[#c9a96e]">Navigation</h3>
                <ul class="space-y-3 text-[#d8d1c4]">
                    <li><a href="{{ route('home') }}" class="transition hover:text-white">Accueil</a></li>
                    <li><a href="{{ route('catalogue') }}" class="transition hover:text-white">Boutique</a></li>
                    <li><a href="{{ route('panier') }}" class="transition hover:text-white">Panier</a></li>
                    <li><a href="{{ route('checkout') }}" class="transition hover:text-white">Commander</a></li>
                    <li><a href="{{ route('contact') }}" class="transition hover:text-white">Contact</a></li>
                </ul>
            </div>

            <div>
                <h3 class="label-caps mb-4 text-[#c9a96e]">Contact</h3>
                <ul class="space-y-3 text-[#d8d1c4]">
                    <li>{{ $address }}</li>
                    @if ($phone)
                        <li><a href="tel:{{ $phone }}" class="transition hover:text-white">{{ $phone }}</a></li>
                    @endif
                    <li><a href="{{ $waLink }}" target="_blank" rel="noopener noreferrer" class="transition hover:text-white">Commander via WhatsApp</a></li>
                    @if ($email)
                        <li><a href="mailto:{{ $email }}" class="transition hover:text-white">{{ $email }}</a></li>
                    @endif
                </ul>

                @if ($instagram || $tiktok)
                    <div class="mt-6 flex items-center gap-3">
                        @if ($instagram)
                            <a href="{{ $instagram }}" target="_blank" rel="noopener noreferrer" class="inline-flex h-10 items-center rounded-full border border-white/15 px-4 text-xs uppercase tracking-[0.14em] text-[#d8d1c4] transition hover:border-[#c9a96e] hover:text-[#c9a96e]">Instagram</a>
                        @endif
                        @if ($tiktok)
                            <a href="{{ $tiktok }}" target="_blank" rel="noopener noreferrer" class="inline-flex h-10 items-center rounded-full border border-white/15 px-4 text-xs uppercase tracking-[0.14em] text-[#d8d1c4] transition hover:border-[#c9a96e] hover:text-[#c9a96e]">TikTok</a>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <div class="mt-10 flex flex-col gap-2 border-t border-white/10 pt-6 text-xs uppercase tracking-[0.14em] text-[#9f9584] sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ now()->year }} {{ $brandName }}</p>
            <p>Storefront redesign integre sur Laravel</p>
        </div>
    </div>
</footer>
