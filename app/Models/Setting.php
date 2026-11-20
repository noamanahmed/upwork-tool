<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;

class Setting extends BaseModel
{
    public const AI_PROVIDERS = [
        'gemini' => [
            'name' => 'Google Gemini',
            'default_model' => 'gemini-flash-latest',
            'popular_models' => ['gemini-flash-latest', 'gemini-1.5-flash', 'gemini-1.5-pro', 'gemini-2.0-flash', 'gemini-2.5-flash'],
            'default_enabled' => true,
            'key_label' => 'Gemini API Key',
            'key_placeholder' => 'AIzaSy...',
            'key_encrypted' => true,
        ],
        'openai' => [
            'name' => 'OpenAI',
            'default_model' => 'gpt-4o',
            'popular_models' => ['gpt-4o', 'gpt-4o-mini', 'gpt-4-turbo', 'gpt-3.5-turbo', 'o1', 'o3-mini'],
            'default_enabled' => false,
            'key_label' => 'OpenAI API Key',
            'key_placeholder' => 'sk-...',
            'key_encrypted' => true,
        ],
        'anthropic' => [
            'name' => 'Anthropic Claude',
            'default_model' => 'claude-3-5-sonnet-20241022',
            'popular_models' => ['claude-3-5-sonnet-20241022', 'claude-3-5-haiku-20241022', 'claude-3-opus-20240229'],
            'default_enabled' => false,
            'key_label' => 'Anthropic API Key',
            'key_placeholder' => 'sk-ant-...',
            'key_encrypted' => true,
        ],
        'groq' => [
            'name' => 'Groq',
            'default_model' => 'llama-3.3-70b-versatile',
            'popular_models' => ['llama-3.3-70b-versatile', 'llama-3.1-8b-instant', 'mixtral-8x7b-32768'],
            'default_enabled' => false,
            'key_label' => 'Groq API Key',
            'key_placeholder' => 'gsk_...',
            'key_encrypted' => true,
        ],
        'deepseek' => [
            'name' => 'DeepSeek',
            'default_model' => 'deepseek-chat',
            'popular_models' => ['deepseek-chat', 'deepseek-reasoner'],
            'default_enabled' => false,
            'key_label' => 'DeepSeek API Key',
            'key_placeholder' => 'sk-...',
            'key_encrypted' => true,
        ],
        'xai' => [
            'name' => 'xAI Grok',
            'default_model' => 'grok-2-latest',
            'popular_models' => ['grok-2-latest', 'grok-beta'],
            'default_enabled' => false,
            'key_label' => 'xAI API Key',
            'key_placeholder' => 'xai-...',
            'key_encrypted' => true,
        ],
        'mistral' => [
            'name' => 'Mistral AI',
            'default_model' => 'mistral-large-latest',
            'popular_models' => ['mistral-large-latest', 'mistral-small-latest', 'codestral-latest'],
            'default_enabled' => false,
            'key_label' => 'Mistral API Key',
            'key_placeholder' => '...',
            'key_encrypted' => true,
        ],
        'ollama' => [
            'name' => 'Ollama (Local)',
            'default_model' => 'llama3',
            'popular_models' => ['llama3', 'llama3.2', 'mistral', 'deepseek-r1', 'qwen2.5'],
            'default_enabled' => false,
            'key_label' => 'Ollama API Key (Optional)',
            'key_placeholder' => 'Optional for local Ollama',
            'key_encrypted' => false,
            'extra_fields' => [
                'OLLAMA_URL' => [
                    'label' => 'Ollama Host URL',
                    'default' => 'http://localhost:11434',
                    'placeholder' => 'http://localhost:11434',
                    'encrypted' => false,
                ],
            ],
        ],
        'openrouter' => [
            'name' => 'OpenRouter',
            'default_model' => 'meta-llama/llama-3.3-70b-instruct',
            'popular_models' => ['meta-llama/llama-3.3-70b-instruct', 'anthropic/claude-3.5-sonnet', 'openai/gpt-4o', 'deepseek/deepseek-r1'],
            'default_enabled' => false,
            'key_label' => 'OpenRouter API Key',
            'key_placeholder' => 'sk-or-...',
            'key_encrypted' => true,
        ],
        'azure' => [
            'name' => 'Azure OpenAI',
            'default_model' => 'gpt-4o',
            'popular_models' => ['gpt-4o', 'gpt-4o-mini', 'gpt-4'],
            'default_enabled' => false,
            'key_label' => 'Azure API Key',
            'key_placeholder' => '...',
            'key_encrypted' => true,
            'extra_fields' => [
                'AZURE_OPENAI_URL' => [
                    'label' => 'Azure Endpoint URL',
                    'default' => '',
                    'placeholder' => 'https://<resource>.openai.azure.com/',
                    'encrypted' => false,
                ],
                'AZURE_OPENAI_DEPLOYMENT' => [
                    'label' => 'Deployment Name',
                    'default' => 'gpt-4o',
                    'placeholder' => 'gpt-4o',
                    'encrypted' => false,
                ],
                'AZURE_OPENAI_API_VERSION' => [
                    'label' => 'API Version',
                    'default' => '2025-04-01-preview',
                    'placeholder' => '2025-04-01-preview',
                    'encrypted' => false,
                ],
            ],
        ],
        'bedrock' => [
            'name' => 'AWS Bedrock',
            'default_model' => 'anthropic.claude-3-5-sonnet-20241022-v2:0',
            'popular_models' => ['anthropic.claude-3-5-sonnet-20241022-v2:0', 'anthropic.claude-3-haiku-20240307-v1:0'],
            'default_enabled' => false,
            'key_label' => 'Bedrock API Key / Bearer Token',
            'key_placeholder' => '...',
            'key_encrypted' => true,
            'extra_fields' => [
                'AWS_BEDROCK_REGION' => [
                    'label' => 'AWS Region',
                    'default' => 'us-east-1',
                    'placeholder' => 'us-east-1',
                    'encrypted' => false,
                ],
                'AWS_ACCESS_KEY_ID' => [
                    'label' => 'AWS Access Key ID',
                    'default' => '',
                    'placeholder' => 'AKIA...',
                    'encrypted' => true,
                ],
                'AWS_SECRET_ACCESS_KEY' => [
                    'label' => 'AWS Secret Access Key',
                    'default' => '',
                    'placeholder' => '...',
                    'encrypted' => true,
                ],
            ],
        ],
    ];

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

    /**
     * In-memory cache for the current request / process lifecycle.
     */
    protected static ?array $runtimeCache = null;

    protected static function booted(): void
    {
        static::saved(function () {
            static::clearCache();
            static::applyToConfig();
        });

        static::deleted(function () {
            static::clearCache();
            static::applyToConfig();
        });
    }

    /**
     * Clear both runtime in-memory cache and persistent framework cache.
     */
    public static function clearCache(): void
    {
        static::$runtimeCache = null;

        try {
            Cache::forget('app_settings_map');
        } catch (\Throwable $e) {
            // Ignore if cache service unavailable
        }
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
            if (static::$runtimeCache === null) {
                static::$runtimeCache = Cache::rememberForever('app_settings_map', function () {
                    if (!Schema::hasTable('settings')) {
                        return [];
                    }

                    return static::query()
                        ->get(['key', 'name', 'value', 'default_value', 'is_encrypted'])
                        ->mapWithKeys(function ($item) {
                            $k = $item->key ?: $item->name;
                            return [$k => [
                                'value' => $item->value,
                                'default_value' => $item->default_value,
                                'is_encrypted' => (bool) $item->is_encrypted,
                            ]];
                        })
                        ->all();
                });
            }

            $setting = static::$runtimeCache[$key] ?? null;
            if ($setting) {
                $val = $setting['value'];
                if ($val !== null && $val !== '') {
                    if ($setting['is_encrypted']) {
                        try {
                            return Crypt::decryptString($val);
                        } catch (\Throwable $e) {
                            return $val;
                        }
                    }
                    return $val;
                }

                if ($setting['default_value'] !== null && $setting['default_value'] !== '') {
                    return $setting['default_value'];
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

            // Available and Enabled AI Providers
            $availableProviders = array_keys(static::AI_PROVIDERS);
            config(['services.ai.available_providers' => $availableProviders]);

            $enabledProviders = [];
            foreach ($availableProviders as $p) {
                $settingVal = static::get('AI_' . strtoupper($p) . '_ENABLED');
                $enabled = false;
                if ($settingVal !== null) {
                    $enabled = filter_var($settingVal, FILTER_VALIDATE_BOOLEAN);
                } else {
                    $defEnabled = static::AI_PROVIDERS[$p]['default_enabled'] ?? false;
                    $enabled = filter_var(env('AI_' . strtoupper($p) . '_ENABLED', $defEnabled), FILTER_VALIDATE_BOOLEAN);
                }

                if ($enabled) {
                    $enabledProviders[] = $p;
                }
            }

            // Ensure the active default provider is included if set
            $defaultProvider = static::get('AI_PROVIDER') ?? config('services.ai.default', 'gemini');
            if ($defaultProvider && !in_array($defaultProvider, $enabledProviders, true)) {
                $enabledProviders[] = $defaultProvider;
            }

            config(['services.ai.enabled_providers' => array_values(array_unique($enabledProviders))]);

            // Apply configuration for each provider
            foreach (static::AI_PROVIDERS as $p => $meta) {
                $key = static::get(strtoupper($p) . '_API_KEY');
                if ($key !== null) {
                    config(["services.ai.{$p}.key" => $key]);
                    config(["ai.providers.{$p}.key" => $key]);
                }

                $model = static::get(strtoupper($p) . '_MODEL');
                if ($model !== null) {
                    config(["services.ai.{$p}.model" => $model]);
                } elseif (!config("services.ai.{$p}.model")) {
                    config(["services.ai.{$p}.model" => $meta['default_model']]);
                }

                $conversationId = static::get(strtoupper($p) . '_CONVERSATION_ID');
                if ($conversationId !== null) {
                    config(["services.ai.{$p}.conversation_id" => $conversationId]);
                } elseif (!config("services.ai.{$p}.conversation_id")) {
                    config(["services.ai.{$p}.conversation_id" => $p]);
                }

                // Provider specific settings
                if ($p === 'azure') {
                    if ($url = static::get('AZURE_OPENAI_URL')) {
                        config(['ai.providers.azure.url' => $url]);
                    }
                    if ($dep = static::get('AZURE_OPENAI_DEPLOYMENT')) {
                        config(['ai.providers.azure.deployment' => $dep]);
                    }
                    if ($ver = static::get('AZURE_OPENAI_API_VERSION')) {
                        config(['ai.providers.azure.api_version' => $ver]);
                    }
                } elseif ($p === 'bedrock') {
                    if ($reg = static::get('AWS_BEDROCK_REGION')) {
                        config(['ai.providers.bedrock.region' => $reg]);
                    }
                    if ($ak = static::get('AWS_ACCESS_KEY_ID')) {
                        config(['ai.providers.bedrock.access_key_id' => $ak]);
                    }
                    if ($sk = static::get('AWS_SECRET_ACCESS_KEY')) {
                        config(['ai.providers.bedrock.secret_access_key' => $sk]);
                    }
                } elseif ($p === 'ollama') {
                    if ($url = static::get('OLLAMA_URL')) {
                        config(['ai.providers.ollama.url' => $url]);
                    }
                }
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
