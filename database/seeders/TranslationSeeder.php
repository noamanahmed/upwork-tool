<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\Role;
use App\Models\Translation;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TranslationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $languages = [
        //     'en-USA' => 'english', // Adjust the mapping based on your requirement
        //     'nl' => 'dutch',
        //     // Add more languages as needed
        // ];

        $languages = Language::all()->pluck('code', 'id')->toArray();

        foreach ($languages as $langId =>  $langCode) {
            $defaultTranslations = (array) $this->loadTranslations($langCode, 'default');
            $overrideTranslations = (array) $this->loadTranslations($langCode, 'override');
            $translations = [...$defaultTranslations,...$overrideTranslations];
            if (empty($translations)) continue;
            foreach ($translations as $key => $value) {
                $this->seedTranslations($langId, $key, $value);
            }
        }
    }
    private function loadTranslations($code, $default)
    {
        $file = base_path("resources/lang/$code/$default.php");
        if (file_exists($file)) return include $file;

        // If the file doesn't exist, try with the first part of the language code
        $parts = explode('-', $code);
        $fallbackCode = $parts[0];
        $file = base_path("resources/lang/$fallbackCode/$default.php");
        if (file_exists($file)) return include $file;

        // If the file doesn't exist, try with the first part of the language code
        $parts = explode('_', $code);
        $fallbackCode = $parts[0];
        $file = base_path("resources/lang/$fallbackCode/$default.php");
        if (file_exists($file)) return include $file;



        return null;
    }
    private function seedTranslations($langId, $key, $value, $parentKey = '')
    {
        if (!is_array($value)) {
            $translationKey = $parentKey ? "$parentKey.$key" : $key;
            $translation = Translation::firstOrNew([
                'key' => $key,
                'language_id' => $langId,
            ]);
            $translation->value = $value;
            $translation->save();
            return;
        }
        foreach ($value as $subKey => $subValue) {
            $translationKey = $parentKey ? "$parentKey.$subKey" : $subKey;

            if (is_array($subValue)) {
                $this->seedTranslations($langId, $key, $subValue, $translationKey);
            } else {
                $translationKey = $key.'.'.$translationKey;
                $translation = Translation::firstOrNew([
                    'key' => $translationKey,
                    'language_id' => $langId,
                ]);
                $translation->value = $subValue;
                $translation->save();
            }
        }
    }
}
