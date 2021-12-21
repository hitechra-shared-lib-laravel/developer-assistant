<?php

namespace HitechraSharedLibLaravel\DeveloperAssistant\Console\Commands\Development;

use HitechraSharedLibLaravel\DeveloperAssistant\Console\Commands\Development\Printer\DefaultPrinter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\PhpFile;
use SplFileInfo;

class BulkSyncModelFillable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hitechra:sync-model-fillable {names?*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync model fillable with databse table';

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

        $this->alert('Starting to sync model fillable...');

        $names = empty($names) ? File::files(app_path('Models')) : $names;

        if ($names[0] instanceof SplFileInfo) {
            $names = collect($names)->map(function (SplFileInfo $i) {
                return str_replace('.php', '', $i->getFilename());
            })->toArray();
        }

        foreach ($names as $name) {
            try {
                $this->syncModelFillable($name);

                $this->info($name . ' synced successfully');
                $success++;
            } catch (\Throwable $th) {
                $this->error($name . ' failed to sync because ' . $th->getMessage());
                $error++;
            }
        }

        $this->comment(sprintf('Model fillable has been synced with : %d success, %d error', $success, $error));

        return 0;
    }

    public function syncModelFillable($model_name)
    {
        $model = 'App\\Models\\' . $model_name;
        $columns = DB::getSchemaBuilder()->getColumnListing((new $model)->getTable());

        $fillable = collect($columns)->filter(fn ($i) => !in_array($i, ['created_at', 'updated_at', 'deleted_at', 'id']))
            ->sort()
            ->map(function ($i) {
                return "'" . $i . "'";
            })->implode(', ');

        $fileName = app_path('Models/' . $model_name . '.php');

        $class = PhpFile::fromCode(file_get_contents($fileName));
        $class->getClasses()[$model]
            ->getProperties()['fillable']
            ->setValue(new Literal('[' . $fillable . ']'));

        $printer = new DefaultPrinter;

        file_put_contents($fileName, $printer->printFile($class));

        return true;
    }
}
