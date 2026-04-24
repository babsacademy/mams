<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $zones = [
            ['value' => 'dakar', 'label' => 'Dakar - livraison 24h', 'price' => 2000],
            ['value' => 'banlieue', 'label' => 'Banlieue Dakar - 24/48h', 'price' => 3000],
            ['value' => 'regions', 'label' => 'Regions - 2 a 3 jours', 'price' => 3500],
        ];

        $settings = [
            ['key' => 'shipping_zones', 'value' => json_encode($zones), 'group' => 'livraison'],
            ['key' => 'free_shipping_threshold', 'value' => '150000', 'group' => 'livraison'],
            ['key' => 'site_name', 'value' => 'Mams Store World', 'group' => 'general'],
            ['key' => 'shop_name', 'value' => 'Mams Store World', 'group' => 'general'],
            ['key' => 'site_tagline', 'value' => 'Hair, beauty, style', 'group' => 'general'],
            ['key' => 'whatsapp_number', 'value' => '221771831987', 'group' => 'contact'],
            ['key' => 'phone_primary', 'value' => '+221 77 183 19 87', 'group' => 'contact'],
            ['key' => 'contact_email', 'value' => 'hello@mamsstoreworld.com', 'group' => 'contact'],
            ['key' => 'physical_address', 'value' => 'Dakar, Senegal', 'group' => 'contact'],
            ['key' => 'instagram_url', 'value' => 'https://instagram.com/mams_store0', 'group' => 'contact'],
            ['key' => 'tiktok_url', 'value' => 'https://tiktok.com/@maamanjoop', 'group' => 'contact'],
            ['key' => 'logo_url', 'value' => '/branding/gold_on_black.png', 'group' => 'branding'],
            ['key' => 'footer_logo_url', 'value' => '/branding/gold_on_black.png', 'group' => 'branding'],
            ['key' => 'hero_image_url', 'value' => '/mams-template/assets/images/hero.png', 'group' => 'hero'],
            ['key' => 'hero_badge', 'value' => 'Mams Store World', 'group' => 'hero'],
            ['key' => 'hero_title_line1', 'value' => 'Revele ta', 'group' => 'hero'],
            ['key' => 'hero_title_line2', 'value' => 'beaute', 'group' => 'hero'],
            ['key' => 'hero_description', 'value' => 'Hair, beauty, style. Une boutique premium pensee pour une clientele feminine, elegante et confiante.', 'group' => 'hero'],
            ['key' => 'hero_cta1_text', 'value' => 'Decouvrir la collection', 'group' => 'hero'],
            ['key' => 'hero_cta2_text', 'value' => 'Pourquoi nous', 'group' => 'hero'],
            ['key' => 'hero_image_position_x', 'value' => '50', 'group' => 'hero'],
            ['key' => 'hero_image_position_y', 'value' => '20', 'group' => 'hero'],
            ['key' => 'craft_image', 'value' => '/mams-template/assets/images/bifaft.png', 'group' => 'vitrine'],
            ['key' => 'craft_title', 'value' => 'Beautes editoriales africaines', 'group' => 'vitrine'],
            ['key' => 'craft_text', 'value' => 'Une selection orientee beaute premium, transformations glamour et accompagnement client sur mesure.', 'group' => 'vitrine'],
            ['key' => 'craft_badge_line1', 'value' => 'Premium quality', 'group' => 'vitrine'],
            ['key' => 'craft_badge_line2', 'value' => 'Livraison rapide Dakar', 'group' => 'vitrine'],
            ['key' => 'color_primary', 'value' => '#c9a96e', 'group' => 'appearance'],
            ['key' => 'color_primary_hover', 'value' => '#b89557', 'group' => 'appearance'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value'], 'group' => $setting['group']]
            );
        }
    }
}
