<?php

namespace HitechraSharedLibLaravel\DeveloperAssistant\Commands\Development;

use HitechraSharedLibLaravel\DeveloperAssistant\Commands\Development\Printer\DefaultPrinter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Nette\PhpGenerator\PhpFile;
use SplFileInfo;

class GenerateModelTraits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hitechra:generate-model-traits {names?*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate model traits (Attribute, Relation, Method, Scope)';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $names = $this->argument('names');
        $error = 0;
        $success = 0;

        $this->alert('Starting to generate traits...');

        if (empty($names)) {
            $names = File::files(app_path('Models'));
            $names = collect($names)->map(function (SplFileInfo $i) {
                return str_replace('.php', '', $i->getFilename());
            })->toArray();
        }

        foreach ($names as $name) {
            try {
                $this->generateModelTraits($name);

                $this->info($name . ' generated successfully');
                $success++;
            } catch (\Throwable $th) {
                $this->error($name . ' failed to generate because ' . $th->getMessage());
                $error++;
            }
        }

        $this->comment(sprintf('Model traits has been generated with : %d success, %d error', $success, $error));

        return 0;
    }

    public function generateModelTraits($model_name)
    {
        $traits = ['Relation', 'Attribute', 'Scope', 'Method'];
        $printer = new DefaultPrinter;

        $modelFileName = app_path('Models/' . $model_name . '.php');

        foreach ($traits as $trait) {
            $plural_name = Str::plural($trait);
            $traitFileName = app_path('Models/Traits/' . $plural_name . '/' . $model_name  . $trait . '.php');

            if (file_exists($traitFileName)) {
                continue;
            }

            $traitName = 'App\Models\Traits\\' . $plural_name . '\\' . $model_name . $trait;

            $file = new PhpFile;
            $file->addTrait($traitName);
            file_put_contents($traitFileName, $printer->printFile($file));

            $modelClass = PhpFile::fromCode(file_get_contents($modelFileName));
            $modelClass->getNamespaces()['App\Models']->addUse($traitName);
            $modelClass->getClasses()['App\Models\\' . $model_name]->addTrait($traitName);
            file_put_contents($modelFileName, $printer->printFile($modelClass));
        }

        return true;
    }
}
