<?php

namespace App\Console\Commands;

use Artisan;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use ReflectionEnum;


class SeedEnumTranslations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translations:seed-enum';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed translations for ENUMs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $enumsPath = app_path('Enums');
        $enumFiles = File::files($enumsPath);
        $enums = [];

        foreach ($enumFiles as $file) {
            if(strrpos($file->getFilenameWithoutExtension(),'Base') === 0) continue;
            $enums[] = $file->getFilenameWithoutExtension();
        }
        usort($enums, function($a, $b) {
            preg_match('/[A-Z][a-z]*/', $a, $matches_a);
            preg_match('/[A-Z][a-z]*/', $b, $matches_b);
            return strcasecmp($matches_a[0], $matches_b[0]);
        });

        $translations = [];
        foreach($enums as $key => $enum)
        {
            $module = str($enum)->singular()->snake();
            // Find the position of the first underscore
            $firstUnderscorePos = strpos($module, '_');

            // Extract the characters before the first underscore
            $moduleName = str(substr($module, 0, $firstUnderscorePos))->plural();

            // Find the position of the last underscore
            $lastUnderscorePos = strrpos($module, '_');

            // Remove the last underscore and the characters after it
            $enumName = substr($module, $firstUnderscorePos + 1, $lastUnderscorePos - $firstUnderscorePos - 1);
            $translationKey = 'dashboard.modules.'.$moduleName.'.enums.'.$enumName.'.';

            foreach( (new ReflectionEnum('App\\Enums\\'.$enum))->getCases() as $case)
            {
                $translations[ $translationKey . str($case->getName())->lower()] =  str($case->getName())->lower()->title()->replace('_',' ')->value();
            }
        }
        $translations = Arr::undot($translations);
        $langsPath = resource_path('lang');
        $langFiles = File::directories($langsPath);

        foreach($langFiles as $languageFolder )
        {
            $fileName = $languageFolder.'/default.php';
            if(!File::exists($fileName))  continue;
            $existingTranslations = include $fileName;
            foreach($existingTranslations['dashboard']['modules'] ?? [] as $key => $module)
            {
                $existingTranslations['dashboard']['modules'][$key]['enums'] = $translations['dashboard']['modules'][$key]['enums'];
            }
            $phpCode = '<?php' . PHP_EOL . PHP_EOL . 'return ' . str_replace('array (', '[', str_replace(')', ']', var_export($existingTranslations, true))) . ';' . PHP_EOL;

            File::put($fileName,$phpCode);
        }

    }
}
