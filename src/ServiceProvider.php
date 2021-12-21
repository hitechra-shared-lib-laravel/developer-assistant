<?php

namespace HitechraSharedLibLaravel\DeveloperAssistant;

use HitechraSharedLibLaravel\DeveloperAssistant\Console\Commands\Development\BulkMakeModelCommand;
use HitechraSharedLibLaravel\DeveloperAssistant\Console\Commands\Development\BulkSyncModelFillable;
use HitechraSharedLibLaravel\DeveloperAssistant\Console\Commands\Development\GenerateModelTraits;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                BulkMakeModelCommand::class,
                BulkSyncModelFillable::class,
                GenerateModelTraits::class
            ]);
        }
    }
}
