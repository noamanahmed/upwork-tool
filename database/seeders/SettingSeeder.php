<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Default settings dictionary with their keys, default values, encryption requirement, and descriptions.
     */
    public static function defaults(): array
    {
        return [
            // Translation
            'RAPID_API_TRANSLATION_API_KEY' => [
                'default_value' => '',
                'is_encrypted' => true,
                'description' => 'RapidAPI Translation Key for automatic translation',
            ],

            // Upwork OAuth
            'UPWORK_CLIENT_ID' => [
                'default_value' => '',
                'is_encrypted' => false,
                'description' => 'Upwork OAuth Client ID',
            ],
            'UPWORK_CLIENT_SECRET' => [
                'default_value' => '',
                'is_encrypted' => true,
                'description' => 'Upwork OAuth Client Secret',
            ],
            'UPWORK_REDIRECT_URI' => [
                'default_value' => 'http://127.0.0.1:10999',
                'is_encrypted' => false,
                'description' => 'Upwork OAuth Redirect URI base',
            ],

            // Slack
            'SLACK_WEBHOOK_URL' => [
                'default_value' => '',
                'is_encrypted' => true,
                'description' => 'Slack Incoming Webhook URL for job notifications',
            ],

            // Upwork Cron
            'UPWORK_CRON_START' => [
                'default_value' => '21:00',
                'is_encrypted' => false,
                'description' => 'Upwork scheduler start time (Pakistan time)',
            ],
            'UPWORK_CRON_END' => [
                'default_value' => '03:00',
                'is_encrypted' => false,
                'description' => 'Upwork scheduler end time (Pakistan time)',
            ],
            'UPWORK_CRON_DAYS' => [
                'default_value' => 'mon,tue,wed,thu,fri',
                'is_encrypted' => false,
                'description' => 'Active days of the week for Upwork scheduler',
            ],
            'UPWORK_CRON_ENABLED' => [
                'default_value' => '1',
                'is_encrypted' => false,
                'description' => 'Master toggle for Upwork background scheduler',
            ],

            // AI Configuration
            'AI_PROVIDER' => [
                'default_value' => 'gemini',
                'is_encrypted' => false,
                'description' => 'Active default AI provider for proposals',
            ],
            'AI_RATE_LIMIT' => [
                'default_value' => '5',
                'is_encrypted' => false,
                'description' => 'AI requests rate limit',
            ],
            'AI_RATE_LIMIT_INTERVAL' => [
                'default_value' => '60',
                'is_encrypted' => false,
                'description' => 'AI rate limit interval in seconds',
            ],
            'AI_MAX_PARALLEL_REQUESTS' => [
                'default_value' => '10',
                'is_encrypted' => false,
                'description' => 'AI maximum parallel requests',
            ],
            'AI_PARALLEL_REQUEST_TIMEOUT' => [
                'default_value' => '60',
                'is_encrypted' => false,
                'description' => 'AI parallel request timeout in seconds',
            ],

            // Gemini
            'GEMINI_API_KEY' => [
                'default_value' => '',
                'is_encrypted' => true,
                'description' => 'Google Gemini API key',
            ],
            'GEMINI_MODEL' => [
                'default_value' => 'gemini-flash-latest',
                'is_encrypted' => false,
                'description' => 'Gemini model name',
            ],
        ];
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (static::defaults() as $key => $meta) {
            $setting = Setting::where('key', $key)->orWhere('name', $key)->first();
            if (!$setting) {
                $setting = new Setting();
                $setting->key = $key;
                $setting->name = $key;
            }

            $setting->default_value = $meta['default_value'];
            $setting->is_encrypted = $meta['is_encrypted'];
            $setting->description = $meta['description'];

            // If an env value exists and no custom value is in DB, we can optionally leave value null
            // so it dynamically falls back to .env, or if we want to initialize it:
            $setting->save();
        }
    }
}
