<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
class CreateCrud extends Command
{
    /**
     * This will create all the required files for a CRUD
     *
     * @var string
     */
    protected $signature = 'crud:create {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a crud';

    protected $stubsPath = '/stubs';
    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
        $crudName = $this->argument('name');
        $crudPath = "Feature/Modules/{$crudName}";

        // Generate Controller
        Artisan::call('make:controller', [
            'name' => "App\Http\Controllers\Api\V1\\{$crudName}Controller",
            '--type' => 'crud'
        ]);

        Artisan::call('make:model', [
            'name' => $crudName,
        ]);


        Artisan::call('make:request', [
            'name' => 'Store'.$crudName.'Request',
        ]);

        Artisan::call('make:request', [
            'name' => 'Update'.$crudName.'Request',
        ]);

        Artisan::call('make:request', [
            'name' => 'Delete'.$crudName.'Request',
        ]);

        Artisan::call('make:migration', [
            'name' => 'create'.str($crudName)->plural().'_table',
            '-n' => true
        ]);

        Artisan::call('make:factory', [
            'name' => "{$crudName}Factory",
            '--model' => $crudName,
        ]);


        Artisan::call('make:seeder', [
            'name' => "{$crudName}Seeder",
        ]);

        $testPath = base_path("tests/{$crudPath}");
        $this->makeDirectory($testPath);

        $this->copyStub('test.pest',$crudName.'CrudTest',base_path("tests/Feature/Modules/{$crudName}"));
        $this->copyStub('repository',$crudName.'Repository',base_path('app/Repositories'));
        $this->copyStub('service',$crudName.'Service',base_path('app/Services'));
        $this->copyStub('transformer',$crudName.'Transformer',base_path('app/Transformers'));
        $this->copyStub('collectiontransformer',$crudName.'CollectionTransformer',base_path('app/Transformers'));
        $this->copyStub('enum',$crudName.'StatusEnum',base_path('app/Enums'));
        $this->replaceStubVariables(app_path("Http/Controllers/Api/V1/{$crudName}Controller.php"));
        $this->replaceStubVariables(app_path("Services/{$crudName}Service.php"));
        $this->replaceStubVariables(app_path("Repositories/{$crudName}Repository.php"));
        $this->replaceStubVariables(app_path("Transformers/{$crudName}Transformer.php"));
        $this->replaceStubVariables(app_path("Transformers/{$crudName}CollectionTransformer.php"));
        $this->replaceStubVariables(app_path("Enums/{$crudName}StatusEnum.php"));
        $this->replaceStubVariables(base_path("tests/Feature/Modules/{$crudName}/{$crudName}CrudTest.php"));
        $this->replaceStubVariables(base_path("database/seeders/{$crudName}Seeder.php"));

        $this->info('CRUD files generated successfully.');

            //code...
        } catch (\Throwable $th) {
            dd($th->getTrace(),$th->getMessage());
        }
    }
    protected function copyStub($stubName,$stubCrudName,$destinationFolder)
    {
        $stubsFolder = base_path('stubs'); // Replace with the actual path to your stubs folder

        $sourceFilePath = "{$stubsFolder}/{$stubName}.stub";
        $destinationFilePath = "{$destinationFolder}/{$stubCrudName}.php";

        if (!File::exists($destinationFilePath)) {
            File::copy($sourceFilePath, $destinationFilePath);
            $this->info("File '{$sourceFilePath}' copied successfully to '{$destinationFilePath}'.");
        } else {
            $this->info("The file '{$sourceFilePath}' already exists in '{$destinationFilePath}'.");
        }

    }
    protected function replaceStubVariables($filePath)
    {
        $fileContent = File::get($filePath);
        $replaceVariablesArray = [
            'modelName' => $this->argument('name'),
            'model' => strtolower($this->argument('name'))
        ];
        foreach($replaceVariablesArray as $key => $value)
        {
            $fileContent = str_replace('{{ '.$key.' }}', $value, $fileContent);
        }
        // Write the modified content back to the file
        File::put($filePath, $fileContent);
    }
    protected function makeDirectory($path)
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }
}
