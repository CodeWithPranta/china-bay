<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Every admin-editable option with its group. Add rows here and they
     * appear in Admin > Settings automatically.
     *
     * @return array<string, array<string, string>>
     */
    public static function defaults(): array
    {
        return [
            'general' => [
                'site.name' => 'ChinaBay',
                'site.tagline' => 'Quality Meets Connection',
                'site.timezone' => 'Asia/Dhaka',
                'site.date_format' => 'd M Y',
                'site.currency' => 'BDT',
                'site.currency_symbol' => '৳',
            ],
            'theme' => [
                'theme.brand' => '#c8102e',
                'theme.brand_dark' => '#a50d25',
                'theme.ink' => '#1b2233',
                'theme.navy' => '#1c2536',
            ],
            'contact' => [
                'contact.phone' => '+8801849415277',
                'contact.email' => 'info@chinabaybd.com',
                'contact.website' => 'www.chinabaybd.com',
                'contact.hours' => '9am – 10pm',
            ],
            'delivery' => [
                'delivery.default_inside_dhaka' => '60',
                'delivery.default_outside_dhaka' => '100',
            ],
            'payments' => [
                'payments.cod_enabled' => '1',
                'payments.bkash_enabled' => '1',
                'payments.nagad_enabled' => '1',
                'payments.gateway' => 'sslcommerz',
            ],
            'marketing' => [
                'marketing.facebook_pixel' => '',
                'marketing.google_analytics' => '',
                'marketing.referral_bonus_points' => '50',
            ],
            'notifications' => [
                'notifications.sms_provider' => '',
                'notifications.order_sms_template' => 'ChinaBay: order {order_no} placed ({total}). COD. Track: {url}',
            ],
        ];
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (static::defaults() as $group => $pairs) {
            foreach ($pairs as $key => $value) {
                Setting::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
            }
        }

        Setting::flushCache();
    }
}
