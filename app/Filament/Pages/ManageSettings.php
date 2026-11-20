<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Database\Seeders\SettingSeeder;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\HtmlString;

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

        $settings = [
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
            'AI_RATE_LIMIT' => Setting::getInt('AI_RATE_LIMIT', 5),
            'AI_RATE_LIMIT_INTERVAL' => Setting::getInt('AI_RATE_LIMIT_INTERVAL', 60),
            'AI_MAX_PARALLEL_REQUESTS' => Setting::getInt('AI_MAX_PARALLEL_REQUESTS', 10),
            'AI_PARALLEL_REQUEST_TIMEOUT' => Setting::getInt('AI_PARALLEL_REQUEST_TIMEOUT', 60),

            // Global Proposal Prompts (Mustache)
            'AI_SYSTEM_PROMPT' => (string) (Setting::get('AI_SYSTEM_PROMPT') ?? ''),
            'AI_PROMPT_TEMPLATE' => (string) (Setting::get('AI_PROMPT_TEMPLATE') ?? ''),

            // Translation
            'RAPID_API_TRANSLATION_API_KEY' => (string) $translationKey,
        ];

        // Provider specific settings
        foreach (Setting::AI_PROVIDERS as $provider => $meta) {
            $u = strtoupper($provider);
            $settingVal = Setting::get("AI_{$u}_ENABLED");
            $isEnabled = ($settingVal !== null)
                ? filter_var($settingVal, FILTER_VALIDATE_BOOLEAN)
                : filter_var(env("AI_{$u}_ENABLED", $meta['default_enabled']), FILTER_VALIDATE_BOOLEAN);

            $settings["AI_{$u}_ENABLED"] = $isEnabled;
            $settings["{$u}_API_KEY"] = (string) (Setting::get("{$u}_API_KEY") ?? '');
            $settings["{$u}_MODEL"] = (string) (Setting::get("{$u}_MODEL") ?? $meta['default_model']);
            $settings["{$u}_CONVERSATION_ID"] = (string) (Setting::get("{$u}_CONVERSATION_ID") ?? $provider);
            $settings["AI_SYSTEM_PROMPT_{$u}"] = (string) (Setting::get("AI_SYSTEM_PROMPT_{$u}") ?? '');
            $settings["AI_PROMPT_TEMPLATE_{$u}"] = (string) (Setting::get("AI_PROMPT_TEMPLATE_{$u}") ?? '');

            if (!empty($meta['extra_fields'])) {
                foreach ($meta['extra_fields'] as $extraKey => $extraMeta) {
                    $settings[$extraKey] = (string) (Setting::get($extraKey) ?? $extraMeta['default']);
                }
            }
        }

        return $settings;
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
                                Section::make('General AI Configuration')
                                    ->description('Default AI provider and rate limiting / concurrency limits for proposal generation.')
                                    ->schema([
                                        Select::make('AI_PROVIDER')
                                            ->label('Active Default AI Provider')
                                            ->options(collect(Setting::AI_PROVIDERS)->mapWithKeys(fn ($meta, $key) => [
                                                $key => $meta['name'] . ($key === 'gemini' ? ' (Default)' : '')
                                            ])->all())
                                            ->required()
                                            ->helperText('Default AI provider used to generate tailored job proposals.'),

                                        Section::make('Concurrency & Rate Limiting')
                                            ->description('Fine-tune proposal generation execution speed and rate limits.')
                                            ->collapsible()
                                            ->collapsed()
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

                                Section::make('Proposal Prompts & Templates (Mustache Templating)')
                                    ->description('Global system prompt instructions and proposal prompt template. Dynamic Mustache tags allow inserting real-time job and context variables.')
                                    ->schema([
                                        Section::make('Template Tags Reference & Documentation')
                                            ->description('Click to expand the dynamic Mustache tags reference guide.')
                                            ->collapsible()
                                            ->collapsed()
                                            ->schema([
                                                Placeholder::make('mustache_reference')
                                                    ->hiddenLabel()
                                                    ->content(new HtmlString($this->getMustacheGuideHtml())),
                                            ]),

                                        Textarea::make('AI_SYSTEM_PROMPT')
                                            ->label('Default System Prompt (Instructions)')
                                            ->helperText('Agent instructions defining behavior, constraints, and output format. Inherited by all providers unless overridden per-provider below. Supports Mustache tags.')
                                            ->rows(12)
                                            ->columnSpanFull(),

                                        Textarea::make('AI_PROMPT_TEMPLATE')
                                            ->label('Default Proposal Prompt Template')
                                            ->helperText('Main prompt sent to the AI model containing job and freelancer context via Mustache tags {{ tag }}. Inherited by all providers unless overridden below.')
                                            ->rows(16)
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('AI Providers & Credentials')
                                    ->description('Enable or disable providers, manage API keys, models, and configure provider-specific prompt overrides.')
                                    ->schema([
                                        Tabs::make('ProviderSettingsTabs')
                                            ->tabs($this->buildProviderTabs())
                                            ->columnSpanFull(),
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
     * Build tabs for all AI providers.
     */
    protected function buildProviderTabs(): array
    {
        $tabs = [];

        foreach (Setting::AI_PROVIDERS as $provider => $meta) {
            $u = strtoupper($provider);
            $fields = [
                Toggle::make("AI_{$u}_ENABLED")
                    ->label("Enable {$meta['name']}")
                    ->helperText("When enabled, proposals will be generated for new jobs using {$meta['name']}."),

                Grid::make(3)->schema([
                    TextInput::make("{$u}_API_KEY")
                        ->label($meta['key_label'])
                        ->password($meta['key_encrypted'])
                        ->revealable($meta['key_encrypted'])
                        ->placeholder($meta['key_placeholder'])
                        ->helperText($meta['key_encrypted'] ? 'Stored encrypted with AES-256.' : 'Host or optional API key.')
                        ->columnSpan(1),

                    TextInput::make("{$u}_MODEL")
                        ->label('Model')
                        ->placeholder($meta['default_model'])
                        ->datalist($meta['popular_models'] ?? [])
                        ->helperText("Model identifier for {$meta['name']}.")
                        ->columnSpan(1),

                    TextInput::make("{$u}_CONVERSATION_ID")
                        ->label('Conversation ID')
                        ->placeholder($provider)
                        ->helperText('Identifier for agent conversation thread.')
                        ->columnSpan(1),
                ]),
            ];

            if (!empty($meta['extra_fields'])) {
                $extraInputs = [];
                foreach ($meta['extra_fields'] as $extraKey => $extraMeta) {
                    $input = TextInput::make($extraKey)
                        ->label($extraMeta['label'])
                        ->placeholder($extraMeta['placeholder'] ?? $extraMeta['default']);

                    if ($extraMeta['encrypted']) {
                        $input->password()->revealable();
                    }

                    $extraInputs[] = $input;
                }

                $fields[] = Grid::make(count($extraInputs))->schema($extraInputs);
            }

            // Provider-specific prompt overrides
            $fields[] = Section::make("Custom Prompts for {$meta['name']} (Overrides)")
                ->description("Optionally override default prompts specifically for {$meta['name']}. Leave blank to use the Default System Prompt and Proposal Prompt.")
                ->collapsible()
                ->collapsed()
                ->schema([
                    Textarea::make("AI_SYSTEM_PROMPT_{$u}")
                        ->label("Custom System Prompt ({$meta['name']})")
                        ->placeholder('Leave empty to inherit Default System Prompt')
                        ->helperText("Specific system instructions for {$meta['name']}. Supports Mustache tags.")
                        ->rows(8)
                        ->columnSpanFull(),

                    Textarea::make("AI_PROMPT_TEMPLATE_{$u}")
                        ->label("Custom Proposal Prompt Template ({$meta['name']})")
                        ->placeholder('Leave empty to inherit Default Proposal Prompt')
                        ->helperText("Specific proposal prompt template for {$meta['name']}. Supports Mustache tags.")
                        ->rows(10)
                        ->columnSpanFull(),
                ]);

            $tabs[] = Tab::make($meta['name'])
                ->schema($fields);
        }

        return $tabs;
    }

    /**
     * Generate HTML documentation for dynamic Mustache tags.
     */
    protected function getMustacheGuideHtml(): string
    {
        $categories = \App\Services\Template\MustacheEngine::getAvailableTags();
        $html = '<div class="space-y-4 text-xs">';
        $html .= '<p class="text-gray-600 dark:text-gray-400">Use double curly braces <code class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 rounded font-mono font-bold text-primary-600 dark:text-primary-400">{{ tag }}</code> to insert dynamic data from the Upwork job, client, and freelancer profile. Both simple tags and raw tags <code class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 rounded font-mono font-bold text-primary-600 dark:text-primary-400">{{{ tag }}}</code> output literal text suitable for LLMs without HTML escaping.</p>';

        $html .= '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">';
        foreach ($categories as $category => $tags) {
            $html .= '<div class="p-3 bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700/60 rounded-lg space-y-1.5">';
            $html .= '<h4 class="font-bold text-gray-900 dark:text-gray-200 text-xs uppercase tracking-wider pb-1 border-b border-gray-200 dark:border-gray-700">' . htmlspecialchars($category) . '</h4>';
            $html .= '<ul class="space-y-1.5">';
            foreach ($tags as $tag => $description) {
                $html .= '<li class="flex flex-col"><span class="font-mono text-primary-600 dark:text-primary-400 font-semibold select-all">' . htmlspecialchars($tag) . '</span><span class="text-gray-500 dark:text-gray-400 text-[11px] leading-tight">' . htmlspecialchars($description) . '</span></li>';
            }
            $html .= '</ul></div>';
        }
        $html .= '</div></div>';

        return $html;
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
            'AI_RATE_LIMIT' => ['encrypted' => false, 'default' => '5', 'desc' => 'AI requests rate limit'],
            'AI_RATE_LIMIT_INTERVAL' => ['encrypted' => false, 'default' => '60', 'desc' => 'AI rate limit interval in seconds'],
            'AI_MAX_PARALLEL_REQUESTS' => ['encrypted' => false, 'default' => '10', 'desc' => 'AI max parallel requests'],
            'AI_PARALLEL_REQUEST_TIMEOUT' => ['encrypted' => false, 'default' => '60', 'desc' => 'AI parallel request timeout in seconds'],
            'AI_SYSTEM_PROMPT' => ['encrypted' => false, 'default' => '', 'desc' => 'Default system prompt instructions'],
            'AI_PROMPT_TEMPLATE' => ['encrypted' => false, 'default' => '', 'desc' => 'Default proposal prompt template'],
            'RAPID_API_TRANSLATION_API_KEY' => ['encrypted' => true, 'default' => '', 'desc' => 'RapidAPI Translation Key'],
        ];

        foreach (Setting::AI_PROVIDERS as $provider => $meta) {
            $u = strtoupper($provider);
            $settingsConfig["AI_{$u}_ENABLED"] = ['encrypted' => false, 'default' => $meta['default_enabled'] ? '1' : '0', 'desc' => "Enable/disable {$meta['name']}"];
            $settingsConfig["{$u}_API_KEY"] = ['encrypted' => $meta['key_encrypted'], 'default' => '', 'desc' => "{$meta['name']} API key"];
            $settingsConfig["{$u}_MODEL"] = ['encrypted' => false, 'default' => $meta['default_model'], 'desc' => "{$meta['name']} model name"];
            $settingsConfig["{$u}_CONVERSATION_ID"] = ['encrypted' => false, 'default' => $provider, 'desc' => "{$meta['name']} conversation id"];
            $settingsConfig["AI_SYSTEM_PROMPT_{$u}"] = ['encrypted' => false, 'default' => '', 'desc' => "Custom system prompt for {$meta['name']}"];
            $settingsConfig["AI_PROMPT_TEMPLATE_{$u}"] = ['encrypted' => false, 'default' => '', 'desc' => "Custom proposal prompt template for {$meta['name']}"];

            if (!empty($meta['extra_fields'])) {
                foreach ($meta['extra_fields'] as $extraKey => $extraMeta) {
                    $settingsConfig[$extraKey] = ['encrypted' => $extraMeta['encrypted'], 'default' => $extraMeta['default'], 'desc' => "{$meta['name']} {$extraMeta['label']}"];
                }
            }
        }

        foreach ($settingsConfig as $key => $meta) {
            $val = $state[$key] ?? null;

            if ($key === 'UPWORK_CRON_DAYS' && is_array($val)) {
                $val = implode(',', $val);
            } elseif ($key === 'UPWORK_CRON_ENABLED' || (str_starts_with($key, 'AI_') && str_ends_with($key, '_ENABLED'))) {
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
