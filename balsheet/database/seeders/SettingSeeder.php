<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Seed the default application settings.
     */
    public function run(): void
    {
        $defaults = [
            'business_name' => 'Balsheet',
            'business_address' => 'Nairobi, Kenya',
            'business_phone' => '0700 000 000',
            'business_email' => 'info@balsheet.test',
            'business_logo' => null,
            'receipt_footer' => 'Thank you for shopping with us!',
            'receipt_show_logo' => '1',
            'vat_rate' => '16',
            'vat_enabled' => '0',
            'low_stock_threshold' => '5',
            'default_reorder_level' => '5',
        ];

        foreach ($defaults as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
