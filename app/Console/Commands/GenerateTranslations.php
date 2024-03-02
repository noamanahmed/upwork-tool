<?php

namespace App\Console\Commands;

use App\Services\ThirdParty\TranslationService;
use Artisan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateTranslations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translations:generate';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate translations using API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $langsPath = resource_path('lang');
        $langFiles = File::directories($langsPath);

        $baseTranslations = [];
        foreach($langFiles as $languageFolder )
        {
            if(basename($languageFolder) !== 'en') continue;
            $fileName = $languageFolder.'/default.php';
            if(!File::exists($fileName))  continue;
            $baseTranslations = include $fileName;
        }

        foreach($langFiles as $languageFolder )
        {
            $langKey = basename($languageFolder);
            if(basename($languageFolder) === 'en') continue;
            $outputFileName = $languageFolder.'/default.php';
            $newTranslations = app(TranslationService::class)->translateJson(json_encode($baseTranslations),$langKey);
            if(empty($newTranslations))
            {
                 $this->error('There was an error generating translations for ',$outputFileName);
                 continue;
            }
            $phpCode = '<?php' . PHP_EOL . PHP_EOL . 'return ' . str_replace('array (', '[', str_replace(')', ']', var_export($newTranslations, true))) . ';' . PHP_EOL;

            File::put($outputFileName,$phpCode);
        }
    }
}
