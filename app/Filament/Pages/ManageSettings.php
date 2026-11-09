<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Database\Seeders\SettingSeeder;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Settings';

    protected static ?string $title = 'System Settings';

    protected static ?int $navigationSort = 100;

    protected static string $view = 'filament.pages.manage-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->loadCurrentSettings());
    }

    /**
     * Load settings from DB with fallback to .env and defaults.
     */
    protected function loadCurrentSettings(): array
    {
        $cronDays = Setting::get('UPWORK_CRON_DAYS', 'mon,tue,wed,thu,fri');
        if (is_string($cronDays)) {
            $cronDays = array_filter(array_map('trim', explode(',', $cronDays)));
        }

        $translationKey = Setting::get('RAPID_API_TRANSLATION_API_KEY')
            ?? Setting::get('I_TRANSLATION_API_KEY')
            ?? Setting::get('AI_TRANSLATION_API_KEY')
            ?? '';

        return [
            // Upwork OAuth
            'UPWORK_CLIENT_ID' => (string) (Setting::get('UPWORK_CLIENT_ID') ?? ''),
            'UPWORK_CLIENT_SECRET' => (string) (Setting::get('UPWORK_CLIENT_SECRET') ?? ''),
            'UPWORK_REDIRECT_URI' => (string) (Setting::get('UPWORK_REDIRECT_URI') ?? 'http://127.0.0.1:10999'),

            // Slack
            'SLACK_WEBHOOK_URL' => (string) (Setting::get('SLACK_WEBHOOK_URL') ?? ''),

            // Scheduler / Cron
            'UPWORK_CRON_ENABLED' => Setting::getBool('UPWORK_CRON_ENABLED', true),
            'UPWORK_CRON_START' => (string) (Setting::get('UPWORK_CRON_START') ?? '21:00'),
            'UPWORK_CRON_END' => (string) (Setting::get('UPWORK_CRON_END') ?? '03:00'),
            'UPWORK_CRON_DAYS' => (array) $cronDays,

            // AI Configuration
            'AI_PROVIDER' => (string) (Setting::get('AI_PROVIDER') ?? 'gemini'),
            'GEMINI_API_KEY' => (string) (Setting::get('GEMINI_API_KEY') ?? ''),
            'GEMINI_MODEL' => (string) (Setting::get('GEMINI_MODEL') ?? 'gemini-flash-latest'),
            'AI_RATE_LIMIT' => Setting::getInt('AI_RATE_LIMIT', 5),
            'AI_RATE_LIMIT_INTERVAL' => Setting::getInt('AI_RATE_LIMIT_INTERVAL', 60),
            'AI_MAX_PARALLEL_REQUESTS' => Setting::getInt('AI_MAX_PARALLEL_REQUESTS', 10),
            'AI_PARALLEL_REQUEST_TIMEOUT' => Setting::getInt('AI_PARALLEL_REQUEST_TIMEOUT', 60),

            // Translation
            'RAPID_API_TRANSLATION_API_KEY' => (string) $translationKey,
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('SettingsTabs')
                    ->tabs([
                        Tab::make('Upwork OAuth')
                            ->icon('heroicon-o-briefcase')
                            ->schema([
                                TextInput::make('UPWORK_CLIENT_ID')
                                    ->label('Upwork Client ID')
                                    ->placeholder('Enter OAuth Client ID')
                                    ->helperText('OAuth2 Client ID from Upwork Developer Portal')
                                    ->columnSpanFull(),
                                TextInput::make('UPWORK_CLIENT_SECRET')
                                    ->label('Upwork Client Secret')
                                    ->password()
                                    ->revealable()
                                    ->placeholder('Enter OAuth Client Secret')
                                    ->helperText('OAuth2 Client Secret. Stored encrypted with AES-256.')
                                    ->columnSpanFull(),
                                TextInput::make('UPWORK_REDIRECT_URI')
                                    ->label('Upwork Redirect URI')
                                    ->url()
                                    ->placeholder('http://127.0.0.1:10999')
                                    ->helperText('Base callback URL. Default: http://127.0.0.1:10999')
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('Slack Alerts')
                            ->icon('heroicon-o-bell')
                            ->schema([
                                TextInput::make('SLACK_WEBHOOK_URL')
                                    ->label('Slack Webhook URL')
                                    ->password()
                                    ->revealable()
                                    ->placeholder('https://hooks.slack.com/services/...')
                                    ->helperText('Incoming webhook URL for posting real-time job notifications. Stored encrypted.')
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('Upwork Scheduler')
                            ->icon('heroicon-o-clock')
                            ->schema([
                                Toggle::make('UPWORK_CRON_ENABLED')
                                    ->label('Enable Upwork Scheduler')
                                    ->helperText('Master switch for Upwork automated background tasks (search, alerts, AI proposals).'),
                                Grid::make(2)
                                    ->schema([
                                        TimePicker::make('UPWORK_CRON_START')
                                            ->label('Start Time')
                                            ->seconds(false)
                                            ->format('H:i')
                                            ->helperText('Start time in Pakistan time (Asia/Karachi). Default: 21:00'),
                                        TimePicker::make('UPWORK_CRON_END')
                                            ->label('End Time')
                                            ->seconds(false)
                                            ->format('H:i')
                                            ->helperText('End time in Pakistan time (Asia/Karachi). Default: 03:00'),
                                    ]),
                                CheckboxList::make('UPWORK_CRON_DAYS')
                                    ->label('Active Days')
                                    ->options([
                                        'mon' => 'Monday',
                                        'tue' => 'Tuesday',
                                        'wed' => 'Wednesday',
                                        'thu' => 'Thursday',
                                        'fri' => 'Friday',
                                        'sat' => 'Saturday',
                                        'sun' => 'Sunday',
                                    ])
                                    ->columns(4)
                                    ->helperText('Select the days on which Upwork search and notification cron jobs should execute.'),
                            ]),

                        Tab::make('AI & Gemini')
                            ->icon('heroicon-o-sparkles')
                            ->schema([
                                Select::make('AI_PROVIDER')
                                    ->label('Active AI Provider')
                                    ->options([
                                        'gemini' => 'Google Gemini (Default)',
                                        'openai' => 'OpenAI',
                                        'anthropic' => 'Anthropic Claude',
                                        'azure' => 'Azure OpenAI',
                                        'bedrock' => 'AWS Bedrock',
                                        'groq' => 'Groq',
                                        'xai' => 'xAI Grok',
                                        'deepseek' => 'DeepSeek',
                                        'mistral' => 'Mistral AI',
                                        'ollama' => 'Ollama (Local)',
                                        'openrouter' => 'OpenRouter',
                                    ])
                                    ->required()
                                    ->helperText('Default AI provider used to generate tailored job proposals.'),
                                TextInput::make('GEMINI_API_KEY')
                                    ->label('Gemini API Key')
                                    ->password()
                                    ->revealable()
                                    ->placeholder('AIzaSy...')
                                    ->helperText('Google AI Studio API Key. Stored encrypted with AES-256.')
                                    ->columnSpanFull(),
                                TextInput::make('GEMINI_MODEL')
                                    ->label('Gemini Model')
                                    ->placeholder('gemini-flash-latest')
                                    ->datalist([
                                        'gemini-flash-latest',
                                        'gemini-1.5-flash',
                                        'gemini-1.5-pro',
                                        'gemini-2.0-flash',
                                        'gemini-2.5-flash',
                                    ])
                                    ->helperText('Model identifier for Google Gemini API.')
                                    ->columnSpanFull(),
                                Section::make('Concurrency & Rate Limiting')
                                    ->description('Fine-tune proposal generation execution speed and rate limits.')
                                    ->collapsible()
                                    ->schema([
                                        Grid::make(2)->schema([
                                            TextInput::make('AI_RATE_LIMIT')
                                                ->label('Max Requests per Interval')
                                                ->numeric()
                                                ->minValue(1)
                                                ->default(5)
                                                ->helperText('Max AI requests allowed per interval window.'),
                                            TextInput::make('AI_RATE_LIMIT_INTERVAL')
                                                ->label('Rate Limit Interval')
                                                ->numeric()
                                                ->minValue(1)
                                                ->default(60)
                                                ->suffix('seconds')
                                                ->helperText('Window period in seconds.'),
                                            TextInput::make('AI_MAX_PARALLEL_REQUESTS')
                                                ->label('Max Parallel Requests')
                                                ->numeric()
                                                ->minValue(1)
                                                ->default(10)
                                                ->helperText('Maximum concurrent proposal generation jobs.'),
                                            TextInput::make('AI_PARALLEL_REQUEST_TIMEOUT')
                                                ->label('Parallel Request Timeout')
                                                ->numeric()
                                                ->minValue(5)
                                                ->default(60)
                                                ->suffix('seconds')
                                                ->helperText('Timeout per parallel request.'),
                                        ]),
                                    ]),
                            ]),

                        Tab::make('Translation')
                            ->icon('heroicon-o-language')
                            ->schema([
                                TextInput::make('RAPID_API_TRANSLATION_API_KEY')
                                    ->label('RapidAPI Translation Key')
                                    ->password()
                                    ->revealable()
                                    ->placeholder('Enter RapidAPI Translation Key')
                                    ->helperText('Used for automatic multi-language translation. Stored encrypted.')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    /**
     * Save settings to database.
     */
    public function save(): void
    {
        $state = $this->form->getState();

        $settingsConfig = [
            'UPWORK_CLIENT_ID' => ['encrypted' => false, 'default' => '', 'desc' => 'Upwork OAuth Client ID'],
            'UPWORK_CLIENT_SECRET' => ['encrypted' => true, 'default' => '', 'desc' => 'Upwork OAuth Client Secret'],
            'UPWORK_REDIRECT_URI' => ['encrypted' => false, 'default' => 'http://127.0.0.1:10999', 'desc' => 'Upwork OAuth Redirect URI base'],
            'SLACK_WEBHOOK_URL' => ['encrypted' => true, 'default' => '', 'desc' => 'Slack Incoming Webhook URL'],
            'UPWORK_CRON_ENABLED' => ['encrypted' => false, 'default' => '1', 'desc' => 'Master toggle for Upwork scheduler'],
            'UPWORK_CRON_START' => ['encrypted' => false, 'default' => '21:00', 'desc' => 'Upwork scheduler start time'],
            'UPWORK_CRON_END' => ['encrypted' => false, 'default' => '03:00', 'desc' => 'Upwork scheduler end time'],
            'UPWORK_CRON_DAYS' => ['encrypted' => false, 'default' => 'mon,tue,wed,thu,fri', 'desc' => 'Active days for Upwork scheduler'],
            'AI_PROVIDER' => ['encrypted' => false, 'default' => 'gemini', 'desc' => 'Default AI Provider'],
            'GEMINI_API_KEY' => ['encrypted' => true, 'default' => '', 'desc' => 'Google Gemini API key'],
            'GEMINI_MODEL' => ['encrypted' => false, 'default' => 'gemini-flash-latest', 'desc' => 'Gemini model name'],
            'AI_RATE_LIMIT' => ['encrypted' => false, 'default' => '5', 'desc' => 'AI requests rate limit'],
            'AI_RATE_LIMIT_INTERVAL' => ['encrypted' => false, 'default' => '60', 'desc' => 'AI rate limit interval in seconds'],
            'AI_MAX_PARALLEL_REQUESTS' => ['encrypted' => false, 'default' => '10', 'desc' => 'AI max parallel requests'],
            'AI_PARALLEL_REQUEST_TIMEOUT' => ['encrypted' => false, 'default' => '60', 'desc' => 'AI parallel request timeout in seconds'],
            'RAPID_API_TRANSLATION_API_KEY' => ['encrypted' => true, 'default' => '', 'desc' => 'RapidAPI Translation Key'],
        ];

        foreach ($settingsConfig as $key => $meta) {
            $val = $state[$key] ?? null;

            if ($key === 'UPWORK_CRON_DAYS' && is_array($val)) {
                $val = implode(',', $val);
            } elseif ($key === 'UPWORK_CRON_ENABLED') {
                $val = $val ? '1' : '0';
            }

            Setting::set($key, $val, $meta['default'], $meta['encrypted']);
        }

        // Also alias I_TRANSLATION_API_KEY for compatibility
        if (isset($state['RAPID_API_TRANSLATION_API_KEY'])) {
            Setting::set('I_TRANSLATION_API_KEY', $state['RAPID_API_TRANSLATION_API_KEY'], '', true);
        }

        Setting::applyToConfig();

        Notification::make()
            ->title('Settings Saved')
            ->body('Application settings have been updated in the database and applied to runtime configuration.')
            ->success()
            ->send();
    }

    /**
     * Header actions for the page.
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('resetDefaults')
                ->label('Reset to Defaults')
                ->color('gray')
                ->icon('heroicon-o-arrow-path')
                ->requiresConfirmation()
                ->modalHeading('Reset Settings to Defaults')
                ->modalDescription('Are you sure you want to restore all settings to their default values? Custom values in the database will be overwritten.')
                ->action(function () {
                    $defaults = SettingSeeder::defaults();
                    foreach ($defaults as $key => $meta) {
                        Setting::set($key, $meta['default_value'], $meta['default_value'], $meta['is_encrypted']);
                    }
                    Setting::applyToConfig();
                    $this->form->fill($this->loadCurrentSettings());

                    Notification::make()
                        ->title('Settings Reset')
                        ->body('All settings have been restored to their default values.')
                        ->warning()
                        ->send();
                }),
        ];
    }
}
