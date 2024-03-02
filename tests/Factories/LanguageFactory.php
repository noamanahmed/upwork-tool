<?php

namespace Tests\Factories;

use App\Models\Language;

trait LanguageFactory {
    static $language = null;

    function createlanguage()
    {
        static::$language = Language::factory()->create([

        ]);
        return static::$language;
    }

    function makelanguage()
    {
        static::$language = Language::factory()->make([

        ]);
        return static::$language;
    }
}
