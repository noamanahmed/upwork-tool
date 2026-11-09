<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;

class Setting extends BaseModel
{
    protected $fillable = [
        'key',
        'name',
        'value',
        'default_value',
        'is_encrypted',
        'description',
    ];

    protected $casts = [
        'is_encrypted' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget('app_settings_map');
            static::applyToConfig();
        });

        static::deleted(function () {
            Cache::forget('app_settings_map');
            static::applyToConfig();
        });
    }

    /**
     * Set the name attribute and keep key in sync.
     */
    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = $value;
        if (empty($this->attributes['key'])) {
            $this->attributes['key'] = $value;
        }
    }

    /**
     * Set the key attribute and keep name in sync.
     */
    public function setKeyAttribute($value): void
    {
        $this->attributes['key'] = $value;
        if (empty($this->attributes['name'])) {
            $this->attributes['name'] = $value;
        }
    }

    /**
     * Get the name attribute (fallback to key).
     */
    public function getNameAttribute(): ?string
    {
        return $this->attributes['name'] ?? $this->attributes['key'] ?? null;
    }

    /**
     * Get the key attribute (fallback to name).
     */
    public function getKeyAttribute(): ?string
    {
        return $this->attributes['key'] ?? $this->attributes['name'] ?? null;
    }

    /**
     * Get a setting by key, with fallback to DB default_value, then .env, then provided $default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        try {
            $settings = Cache::rememberForever('app_settings_map', function () {
                if (!Schema::hasTable('settings')) {
                    return collect();
                }
                return static::all()->keyBy(fn ($item) => $item->key ?? $item->name);
            });

            $setting = $settings->get($key);
            if ($setting) {
                $val = $setting->value;
                if ($val !== null && $val !== '') {
                    if ($setting->is_encrypted) {
                        try {
                            return Crypt::decryptString($val);
                        } catch (\Throwable $e) {
                            return $val;
                        }
                    }
                    return $val;
                }

                if ($setting->default_value !== null && $setting->default_value !== '') {
                    return $setting->default_value;
                }
            }
        } catch (\Throwable $e) {
            // In case DB is not yet migrated or connection fails
        }

        // Fallback to .env
        $envVal = env($key);
        if ($envVal !== null) {
            return $envVal;
        }

        return $default;
    }

    /**
     * Set/update a setting value in database.
     */
    public static function set(string $key, mixed $value, mixed $defaultValue = null, ?bool $isEncrypted = null): self
    {
        $setting = static::where('key', $key)->orWhere('name', $key)->first();
        if (!$setting) {
            $setting = new static();
            $setting->key = $key;
            $setting->name = $key;
        }

        if ($isEncrypted !== null) {
            $setting->is_encrypted = $isEncrypted;
        }

        if ($defaultValue !== null) {
            $setting->default_value = $defaultValue;
        }

        if ($value === null || $value === '') {
            $setting->value = null;
        } elseif ($setting->is_encrypted) {
            $setting->value = Crypt::encryptString((string) $value);
        } else {
            $setting->value = is_array($value) ? json_encode($value) : (string) $value;
        }

        $setting->save();

        return $setting;
    }

    /**
     * Helper to get boolean setting.
     */
    public static function getBool(string $key, bool $default = false): bool
    {
        $val = static::get($key, $default);
        return filter_var($val, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Helper to get integer setting.
     */
    public static function getInt(string $key, int $default = 0): int
    {
        return (int) static::get($key, $default);
    }

    /**
     * Helper to get array setting (e.g. comma-separated string or array).
     */
    public static function getArray(string $key, array $default = []): array
    {
        $val = static::get($key, null);
        if ($val === null) {
            return $default;
        }
        if (is_array($val)) {
            return $val;
        }
        return array_filter(array_map('trim', explode(',', $val)));
    }

    /**
     * Backward-compatible insertOrUpdate method for UpWorkService.
     */
    public static function insertOrUpdate($name, $value)
    {
        return static::set($name, $value);
    }

    /**
     * Apply DB settings to runtime Laravel config.
     */
    public static function applyToConfig(): void
    {
        try {
            if (!Schema::hasTable('settings')) {
                return;
            }

            // Upwork
            if ($val = static::get('UPWORK_CLIENT_ID')) {
                config(['upwork.client_id' => $val]);
            }
            if ($val = static::get('UPWORK_CLIENT_SECRET')) {
                config(['upwork.client_secret' => $val]);
            }
            if ($val = static::get('UPWORK_REDIRECT_URI')) {
                config(['upwork.redirect_uri' => $val]);
            }

            // Slack
            if ($val = static::get('SLACK_WEBHOOK_URL')) {
                config(['services.slack.webhook_url' => $val]);
            }

            // Cron / Schedule
            $cronEnabled = static::get('UPWORK_CRON_ENABLED');
            if ($cronEnabled !== null) {
                config(['app.upwork_cron.enabled' => filter_var($cronEnabled, FILTER_VALIDATE_BOOLEAN)]);
            }
            if ($val = static::get('UPWORK_CRON_START')) {
                config(['app.upwork_cron.start_time' => $val]);
            }
            if ($val = static::get('UPWORK_CRON_END')) {
                config(['app.upwork_cron.end_time' => $val]);
            }
            if ($val = static::get('UPWORK_CRON_DAYS')) {
                $days = is_array($val) ? $val : array_filter(array_map('trim', explode(',', $val)));
                config(['app.upwork_cron.days' => $days]);
            }

            // AI Provider & Tuning
            if ($val = static::get('AI_PROVIDER')) {
                config(['ai.default' => $val]);
                config(['services.ai.default' => $val]);
                $enabled = config('services.ai.enabled_providers', []);
                if (!in_array($val, $enabled)) {
                    $enabled[] = $val;
                    config(['services.ai.enabled_providers' => $enabled]);
                }
            }
            if ($val = static::get('AI_RATE_LIMIT')) {
                config(['services.ai.rate_limit' => (int) $val]);
            }
            if ($val = static::get('AI_RATE_LIMIT_INTERVAL')) {
                config(['services.ai.rate_limit_interval' => (int) $val]);
            }
            if ($val = static::get('AI_MAX_PARALLEL_REQUESTS')) {
                config(['services.ai.max_parallel_requests' => (int) $val]);
            }
            if ($val = static::get('AI_PARALLEL_REQUEST_TIMEOUT')) {
                config(['services.ai.parallel_request_timeout' => (int) $val]);
            }

            // Gemini
            if ($val = static::get('GEMINI_API_KEY')) {
                config(['services.ai.gemini.key' => $val]);
                config(['ai.providers.gemini.key' => $val]);
            }
            if ($val = static::get('GEMINI_MODEL')) {
                config(['services.ai.gemini.model' => $val]);
            }

            // Translation
            $translationKey = static::get('RAPID_API_TRANSLATION_API_KEY')
                ?? static::get('I_TRANSLATION_API_KEY')
                ?? static::get('AI_TRANSLATION_API_KEY');
            if ($translationKey !== null) {
                config(['rapidapi.translation.api_key' => $translationKey]);
            }
        } catch (\Throwable $e) {
            // Silently fallback if DB not ready
        }
    }
}
