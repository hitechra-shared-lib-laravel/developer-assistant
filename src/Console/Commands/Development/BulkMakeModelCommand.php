<?php

namespace HitechraSharedLibLaravel\DeveloperAssistant\Console\Commands\Development;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class BulkMakeModelCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'hitechra:make-model-bulk
        {--force : Create the class even if the model already exists}
        {--m|migration : Create a new migration file for the model} {names*}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Bulk create new Eloquent model classes';

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

		$this->alert('Starting to create model...');

		foreach ($names as $name) {
			try {
				Artisan::call('make:model', array_merge([
					'name'    => $name,
					'--force' => $this->option('force'),
				]));

				$fileName = app_path('Models/' . $name . '.php');
				$lines = file($fileName, FILE_IGNORE_NEW_LINES);
				$lines[10] = "\n\tprotected \$table = '" . strtolower(Str::snake($name)) . "';";
				$lines[11] = "\n\tprotected \$fillable = ['id'];";
				$lines[12] = '}';
				file_put_contents($fileName, implode("\n", $lines));

				$this->info($name . ' created successfully');
				$success++;
			} catch (\Throwable $th) {
				$this->error($name . ' failed to create because ' . $th->getMessage());
				$error++;
			}
		}

		$this->comment(sprintf('Model creation has been finished with : %d success, %d error', $success, $error));

		if ($this->option('migration')) {
			$this->alert('Starting to create migration...');

			$success = 0;
			$error = 0;

			foreach ($names as $name) {
				try {
					Artisan::call('make:migration', array_merge([
						'name'     => sprintf('Create %s Table', $name),
						'--create' => strtolower(Str::snake($name)),
					]));

					$this->info('Migration for ' . $name . ' created successfully');
					$success++;
				} catch (\Throwable $th) {
					$this->error('Migration for ' . $name . ' failed to create because ' . $th->getMessage());
					$error++;
				}
			}

			$this->comment(sprintf('Migration creation has been finished with : %d success, %d error', $success, $error));
		}

		return 0;
	}
}
