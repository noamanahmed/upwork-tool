<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $languagesData = [
            [
                'name' => 'English',
                'i18nLang' => 'en-USA',
                'icon' => '🇺🇸',
            ],
            [
                'name' => 'Dutch',
                'i18nLang' => 'nl',
                'icon' => '🇳🇱',
            ],
            [
                'name' => 'Italian',
                'i18nLang' => 'it',
                'icon' => '🇮🇹',
                'active' => 0,
            ],
            [
                'name' => 'French',
                'i18nLang' => 'fr',
                'icon' => '🇫🇷',
                'active' => 0,
            ],
            [
                'name' => 'German',
                'i18nLang' => 'de',
                'icon' => '🇩🇪',
                'active' => 0,
            ],
        ];

        foreach ($languagesData as $language) {
            Language::create([
                'name' => $language['name'],
                'code' => $language['i18nLang'],
                'icon' => $language['icon'],
                'active' => $language['active'] ?? 1,
            ]);
        }
    }
}
