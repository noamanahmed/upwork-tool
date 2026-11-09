<?php

use App\Models\Setting;

if (!function_exists('setting')) {
    /**
     * Get or set a system setting.
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    function setting(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return app(Setting::class);
        }

        return Setting::get($key, $default);
    }
}
