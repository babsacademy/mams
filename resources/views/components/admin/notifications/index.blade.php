<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Notifications')] #[Layout('layouts.app')] class extends Component
{
    // WhatsApp
    public string $whatsappNumber = '';

    // Toggles
    public bool $notifyAdminOnOrder  = false;
    public bool $notifyClientOnOrder = false;

    public string $testEmail = '';
    public ?string $message  = null;
    public bool $testSuccess = false;

    public function mount(): void
    {
        $this->whatsappNumber      = Setting::get('whatsapp_number', '');
        $this->notifyAdminOnOrder  = (bool) Setting::get('notify_admin_on_order', '0');
        $this->notifyClientOnOrder = (bool) Setting::get('notify_client_on_order', '0');
        $this->testEmail           = auth()->user()->email;
    }

    public function save(): void
    {
        $this->validate([
            'whatsappNumber' => ['nullable', 'string'],
        ]);

        Setting::set('whatsapp_number', $this->whatsappNumber, 'notifications');
        Setting::set('notify_admin_on_order', $this->notifyAdminOnOrder ? '1' : '0', 'notifications');
        Setting::set('notify_client_on_order', $this->notifyClientOnOrder ? '1' : '0', 'notifications');

        $this->message     = 'Paramètres sauvegardés.';
        $this->testSuccess = true;
    }

    public function sendTestEmail(): void
    {
        $this->validate(['testEmail' => ['required', 'email']]);

        if (! config('mail.mailers.smtp.host')) {
            $this->message     = 'SMTP non configuré. Ajoutez MAIL_HOST dans le fichier .env du serveur.';
            $this->testSuccess = false;

            return;
        }

        try {
            Mail::raw('Test email depuis '.config('app.name').'.', function ($message) {
                $message->to($this->testEmail)->subject('Test SMTP — '.config('app.name'));
            });

            $this->message     = "Email de test envoyé à {$this->testEmail}.";
            $this->testSuccess = true;
        } catch (\Exception $e) {
            $this->message     = 'Erreur SMTP : '.$e->getMessage();
            $this->testSuccess = false;
        }
    }
};
?>

<div>
    {{-- En-tête --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-black text-zinc-900 dark:text-white tracking-tight">Notifications</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">Alertes commandes et WhatsApp</p>
        </div>
        <flux:button wire:click="save" variant="primary" icon="check" class="!rounded-xl">
            Sauvegarder
        </flux:button>
    </div>

    @if($message)
    <div class="mb-6 px-4 py-3 rounded-xl text-sm font-semibold {{ $testSuccess ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400' }}">
        {{ $message }}
    </div>
    @endif

    {{-- Info .env --}}
    <div class="mb-6 flex items-start gap-3 bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/20 rounded-xl px-4 py-3">
        <flux:icon name="information-circle" class="size-5 text-blue-500 shrink-0 mt-0.5" />
        <div class="text-sm text-blue-700 dark:text-blue-300">
            <p class="font-semibold mb-1">Configuration SMTP dans le fichier .env</p>
            <p class="text-xs leading-relaxed opacity-80">Les identifiants email (hôte, port, mot de passe) sont à configurer directement dans le fichier <code class="bg-blue-100 dark:bg-blue-900/40 px-1 rounded">.env</code> sur le serveur pour des raisons de sécurité.</p>
            <p class="text-xs mt-1 font-mono opacity-70">MAIL_HOST · MAIL_PORT · MAIL_USERNAME · MAIL_PASSWORD · MAIL_ENCRYPTION</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Alertes commandes --}}
        <div class="bg-white dark:bg-zinc-900/50 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm p-6">
            <h2 class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-wider mb-5 flex items-center gap-2">
                <flux:icon name="bell" class="size-4" />
                Alertes commandes
            </h2>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-zinc-900 dark:text-white">Notifier l'admin</p>
                        <p class="text-xs text-zinc-500">Recevoir un email à chaque nouvelle commande</p>
                    </div>
                    <flux:switch wire:model.live="notifyAdminOnOrder" />
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-zinc-900 dark:text-white">Confirmation client</p>
                        <p class="text-xs text-zinc-500">Envoyer un email de confirmation au client</p>
                    </div>
                    <flux:switch wire:model.live="notifyClientOnOrder" />
                </div>
            </div>

            {{-- Test email --}}
            <div class="mt-5 pt-5 border-t border-zinc-100 dark:border-zinc-800">
                <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider mb-3">Tester la connexion SMTP</p>
                <div class="flex gap-2">
                    <flux:input wire:model="testEmail" placeholder="Email de test" class="flex-1" />
                    <flux:button wire:click="sendTestEmail" wire:loading.attr="disabled" variant="ghost" class="!rounded-xl shrink-0">
                        <span wire:loading.remove wire:target="sendTestEmail">Envoyer</span>
                        <span wire:loading wire:target="sendTestEmail">Envoi...</span>
                    </flux:button>
                </div>
            </div>
        </div>

        {{-- WhatsApp --}}
        <div class="bg-white dark:bg-zinc-900/50 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm p-6">
            <h2 class="text-sm font-black text-zinc-900 dark:text-white uppercase tracking-wider mb-5 flex items-center gap-2">
                <flux:icon name="chat-bubble-left-ellipsis" class="size-4" />
                WhatsApp Business
            </h2>
            <flux:field>
                <flux:label>Numéro WhatsApp</flux:label>
                <flux:input wire:model="whatsappNumber" placeholder="221771831987 (sans +)" />
                <flux:description>Entrez le numéro sans le + (ex: 221771831987)</flux:description>
            </flux:field>
        </div>

    </div>
</div>
