@extends('layouts.shop')

@section('title', 'Suivre ma commande | Prosmax by SS')
@section('description', 'Suivez l$item['quantity']état de votre commande Premium Prosmax by SS.')

@php
    $whatsapp = $siteInfo['whatsapp_number'] ?? '221770000000';
    $waNumber = ltrim(preg_replace('/[^\d]/', '', $whatsapp), '+');
@endphp

@section('content')

{{-- ── Hero Section ── --}}
<section class="bg-gradient-to-br from-black to-gray-900 py-24 sm:py-32 relative overflow-hidden">
  <div class="absolute inset-0 opacity-10 bg-cover bg-center"
       style="background-image: url('{{ asset('assets/images/og-image.svg') }}')"></div>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 relative z-10 text-center">
    <p class="text-accent text-xs uppercase tracking-[0.3em] font-bold mb-4">Service Client</p>
    <h1 class="font-display text-4xl sm:text-5xl md:text-6xl font-extrabold uppercase tracking-tight text-white mb-6">
      Suivre ma <br><span class="text-gray-500">commande</span>
    </h1>
    <p class="text-gray-400 max-w-lg mx-auto font-light leading-relaxed">
      Entrez le numéro de référence fourni lors de la validation pour consulter l'état d'avancement de votre commande.
    </p>
  </div>
</section>

{{-- ── Form Section ── --}}
<section class="py-16 px-4" x-data="trackingData()">
  <div class="max-w-2xl mx-auto">
    <div class="bg-white p-8 sm:p-12 shadow-xl shadow-black/5 -mt-32 relative z-20 border border-gray-100">

      {{-- Search Form --}}
      <form @submit.prevent="trackOrder" x-show="!isTracking">
        <div class="mb-6">
          <label for="orderNumber"
                 class="block text-xs font-bold uppercase tracking-widest text-gray-500 mb-3">
            Numéro de référence (ex: CMD-20260305-XXXXXX)
          </label>
          <input type="text" id="orderNumber" x-model="orderInput" required placeholder="CMD-..."
                 class="w-full bg-gray-50 border border-gray-200 px-6 py-4 text-sm font-bold tracking-widest focus:outline-none focus:border-black focus:ring-1 focus:ring-black uppercase transition-colors placeholder:font-normal placeholder:normal-case">
        </div>
        <button type="submit"
                class="w-full bg-black text-white px-8 py-4 text-xs font-bold uppercase tracking-widest hover:bg-gray-900 transition-colors flex justify-center items-center h-[52px]">
          <span x-show="!loading">Rechercher la commande</span>
          <svg x-show="loading" class="animate-spin h-5 w-5 text-white"
               xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" x-cloak>
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
            </path>
          </svg>
        </button>

        <p x-show="error" class="text-red-500 text-xs font-bold mt-4 text-center tracking-wide" x-cloak
           x-text="errorMessage"></p>
      </form>

      {{-- Tracking Result (Simulated) --}}
      <div x-show="isTracking" x-transition:enter="transition ease-out duration-500 transform"
           x-transition:enter-start="opacity-0 translate-y-4"
           x-transition:enter-end="opacity-100 translate-y-0" x-cloak>

        <div class="flex items-start justify-between mb-8 pb-6 border-b border-gray-100">
          <div>
            <p class="text-[10px] text-gray-400 uppercase tracking-widest font-bold mb-1">Commande trouvée</p>
            <h3 class="font-display font-extrabold text-2xl uppercase tracking-widest text-black"
                x-text="orderInput"></h3>
          </div>
          <span class="inline-flex px-3 py-1 text-[10px] font-bold uppercase tracking-widest"
                :class="orderData.status === 'cancelled' ? 'bg-red-100 text-red-600' : 'bg-accent/10 text-accent'"
                x-text="orderData.status_label"></span>
        </div>

        {{-- Stepper progress --}}
        <div class="relative mb-10" x-show="orderData.status !== 'cancelled'">
          <div class="absolute left-6 top-6 bottom-6 w-px bg-gray-100"></div>

          {{-- Step 1: Commande confirmée --}}
          <div class="relative flex items-start mb-8 gap-4" :class="orderData.step >= 0 ? '' : 'opacity-40'">
            <template x-if="orderData.step >= 1">
              <div class="w-12 h-12 bg-black text-white rounded-full flex items-center justify-center shrink-0 z-10 shadow-md">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
              </div>
            </template>
            <template x-if="orderData.step === 0">
              <div class="w-12 h-12 border-2 border-accent bg-white text-accent rounded-full flex items-center justify-center shrink-0 z-10">
                <span class="w-3 h-3 bg-accent rounded-full animate-pulse"></span>
              </div>
            </template>
            <div class="pt-3">
              <p class="font-bold text-sm uppercase tracking-widest text-black">Commande reçue</p>
              <p class="text-xs text-gray-500 mt-1" x-text="orderData.placed_at ? 'Le ' + orderData.placed_at : 'En attente de confirmation.'"></p>
            </div>
          </div>

          {{-- Step 2: Préparation / Confirmée --}}
          <div class="relative flex items-start mb-8 gap-4" :class="orderData.step >= 1 ? '' : 'opacity-40'">
            <template x-if="orderData.step >= 2">
              <div class="w-12 h-12 bg-black text-white rounded-full flex items-center justify-center shrink-0 z-10 shadow-md">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
              </div>
            </template>
            <template x-if="orderData.step === 1">
              <div class="w-12 h-12 border-2 border-accent bg-white text-accent rounded-full flex items-center justify-center shrink-0 z-10">
                <span class="w-3 h-3 bg-accent rounded-full animate-pulse"></span>
              </div>
            </template>
            <template x-if="orderData.step < 1">
              <div class="w-12 h-12 bg-gray-100 text-gray-400 rounded-full flex items-center justify-center shrink-0 z-10">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="square" stroke-linejoin="miter" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
            </template>
            <div class="pt-3">
              <p class="font-bold text-sm uppercase tracking-widest text-black">Préparation en cours</p>
              <p class="text-xs text-gray-500 mt-1 max-w-sm">Nos équipes préparent soigneusement vos vêtements streetwear.</p>
            </div>
          </div>

          {{-- Step 3: Expédiée --}}
          <div class="relative flex items-start mb-8 gap-4" :class="orderData.step >= 2 ? '' : 'opacity-40'">
            <template x-if="orderData.step >= 3">
              <div class="w-12 h-12 bg-black text-white rounded-full flex items-center justify-center shrink-0 z-10 shadow-md">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
              </div>
            </template>
            <template x-if="orderData.step === 2">
              <div class="w-12 h-12 border-2 border-accent bg-white text-accent rounded-full flex items-center justify-center shrink-0 z-10">
                <span class="w-3 h-3 bg-accent rounded-full animate-pulse"></span>
              </div>
            </template>
            <template x-if="orderData.step < 2">
              <div class="w-12 h-12 bg-gray-100 text-gray-400 rounded-full flex items-center justify-center shrink-0 z-10">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="square" stroke-linejoin="miter" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
            </template>
            <div class="pt-3">
              <p class="font-bold text-sm uppercase tracking-widest text-black">En cours de livraison</p>
              <p class="text-xs text-gray-500 mt-1">Un agent vous contactera pour organiser la remise.</p>
            </div>
          </div>

          {{-- Step 4: Livrée --}}
          <div class="relative flex items-start gap-4" :class="orderData.step >= 3 ? '' : 'opacity-40'">
            <template x-if="orderData.step >= 3">
              <div class="w-12 h-12 bg-accent text-white rounded-full flex items-center justify-center shrink-0 z-10 shadow-md">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
              </div>
            </template>
            <template x-if="orderData.step < 3">
              <div class="w-12 h-12 bg-gray-100 text-gray-400 rounded-full flex items-center justify-center shrink-0 z-10">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="square" stroke-linejoin="miter" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
              </div>
            </template>
            <div class="pt-3">
              <p class="font-bold text-sm uppercase tracking-widest text-black">Livrée</p>
              <p class="text-xs text-gray-500 mt-1">Commande remise avec succès. Merci pour votre confiance !</p>
            </div>
          </div>
        </div>

        {{-- Cancelled state --}}
        <div x-show="orderData.status === 'cancelled'" class="mb-10 bg-red-50 border border-red-200 p-6 text-center" x-cloak>
          <svg class="w-12 h-12 text-red-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
          <p class="font-bold text-sm uppercase tracking-widest text-red-600">Commande annulée</p>
          <p class="text-xs text-red-500 mt-2">Cette commande a été annulée. Contactez le support pour plus d'informations.</p>
        </div>

        <div class="bg-gray-50 p-6 border border-gray-200">
          <p class="text-[10px] text-gray-500 uppercase tracking-widest font-bold mb-3 text-center">
            Besoin de modifier ou annuler ?
          </p>
          <a :href="'https://wa.me/{{ $waNumber }}?text=' + encodeURIComponent('Bonjour, je souhaite avoir des informations sur ma commande ' + orderInput)"
             target="_blank"
             class="w-full flex items-center justify-center gap-2 bg-[#25D366] text-white px-6 py-4 text-xs font-bold uppercase tracking-widest hover:bg-[#1da152] transition-colors">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
              <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
            </svg>
            Contacter le support WhatsApp
          </a>
        </div>

        <div class="mt-8 text-center">
          <button @click="resetForm"
                  class="text-xs uppercase tracking-widest font-bold text-gray-400 hover:text-black transition-colors underline underline-offset-4">
            Suivre une autre commande
          </button>
        </div>
      </div>

    </div>
  </div>
</section>

@endsection

@push('scripts')
<script>
function trackingData() {
    return {
        orderInput: '',
        isTracking: false,
        loading: false,
        error: false,
        errorMessage: '',
        orderData: {},

        async trackOrder() {
            const cleanInput = this.orderInput.trim().toUpperCase();

            if (cleanInput.length < 5) {
                this.error = true;
                this.errorMessage = 'Veuillez entrer un numéro de commande valide (ex: CMD-20260305-XXXXXX).';
                return;
            }

            this.error = false;
            this.loading = true;

            try {
                const response = await fetch(`/api/orders/${encodeURIComponent(cleanInput)}/track`);

                if (!response.ok) {
                    this.error = true;
                    this.errorMessage = response.status === 404
                        ? 'Aucune commande trouvée avec ce numéro. Vérifiez et réessayez.'
                        : 'Une erreur est survenue. Veuillez réessayer.';
                    this.loading = false;
                    return;
                }

                this.orderData = await response.json();
                this.orderInput = this.orderData.order_number;
                this.isTracking = true;
            } catch (e) {
                this.error = true;
                this.errorMessage = 'Erreur de connexion. Vérifiez votre connexion internet.';
            }

            this.loading = false;
        },

        resetForm() {
            this.isTracking = false;
            this.orderInput = '';
            this.error = false;
            this.errorMessage = '';
            this.orderData = {};
        }
    }
}
</script>
@endpush
