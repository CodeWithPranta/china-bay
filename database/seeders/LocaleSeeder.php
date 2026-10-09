<?php

namespace Database\Seeders;

use App\Models\SiteLocale;
use App\Models\Translation;
use Illuminate\Database\Seeder;

class LocaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locales = [
            ['code' => 'bn', 'name' => 'Bengali', 'native_name' => 'বাংলা', 'direction' => 'ltr', 'is_active' => true, 'is_default' => true, 'sort_order' => 0],
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'direction' => 'ltr', 'is_active' => true, 'is_default' => false, 'sort_order' => 1],
            ['code' => 'ar', 'name' => 'Arabic', 'native_name' => 'العربية', 'direction' => 'rtl', 'is_active' => true, 'is_default' => false, 'sort_order' => 2],
        ];

        foreach ($locales as $row) {
            SiteLocale::updateOrCreate(['code' => $row['code']], $row);
        }

        // Import lang/{locale}/*.php strings as editable DB rows (DB wins over files).
        foreach (glob(lang_path('*/*.php')) ?: [] as $file) {
            $locale = basename(dirname($file));
            $group = basename($file, '.php');
            foreach ((require $file) ?: [] as $key => $value) {
                Translation::updateOrCreate(
                    ['locale' => $locale, 'group' => (string) $group, 'key' => (string) $key],
                    ['value' => (string) $value]
                );
            }
        }

        SiteLocale::flushCache();
    }
}
