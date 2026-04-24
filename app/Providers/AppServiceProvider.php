<?php

namespace App\Providers;

use App\Models\Setting;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureAuthorization();
        $this->registerViewComposers();
    }

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    protected function configureAuthorization(): void
    {
        Gate::before(function (\App\Models\User $user, string $ability): ?bool {
            if ($user->isAdmin()) {
                return true;
            }

            return null;
        });

        Gate::define('admin-action', fn (\App\Models\User $user): bool => $user->isAdmin());
    }

    protected function registerViewComposers(): void
    {
        View::composer(['layouts.shop', 'shop.*'], function ($view): void {
            $mainLogo = Setting::resolveMediaUrl(Setting::get('logo_url', '')) ?? asset('favicon.svg');
            $footerLogo = Setting::resolveMediaUrl(Setting::get('footer_logo_url', '')) ?? $mainLogo;
            $favicon = Setting::resolveMediaUrl(Setting::get('favicon_url', '')) ?? null;

            $siteInfo = [
                'logo_url' => $mainLogo,
                'footer_logo_url' => $footerLogo,
                'favicon_url' => $favicon,
                'whatsapp_number' => Setting::get('whatsapp_number', '221771831987'),
                'phone_primary' => Setting::get('phone_primary', Setting::get('phone', '')),
                'contact_email' => Setting::get('contact_email', ''),
                'physical_address' => Setting::get('physical_address', 'Dakar, Senegal'),
                'instagram_url' => Setting::get('instagram_url', Setting::get('social_instagram', '')),
                'tiktok_url' => Setting::get('tiktok_url', Setting::get('social_tiktok', '')),
                'facebook_url' => Setting::get('facebook_url', Setting::get('social_facebook', '')),
                'shop_name' => Setting::get('shop_name', Setting::get('site_name', 'Mams Store World')),
            ];

            $view->with('siteInfo', $siteInfo);
        });
    }
}
