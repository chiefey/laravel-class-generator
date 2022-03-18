<?php

namespace Chiefey\Generator;

use Chiefey\Generator\Console\Commands\ControllerMakeCommand;
use Chiefey\Generator\Console\Commands\ModelMakeCommand;
use Chiefey\Generator\Console\Commands\RequestMakeCommand;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class GeneratorServiceProvider extends ServiceProvider implements DeferrableProvider
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
                ControllerMakeCommand::class,
                ModelMakeCommand::class,
                RequestMakeCommand::class,
            ]);
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            ControllerMakeCommand::class,
            ModelMakeCommand::class,
            RequestMakeCommand::class,
        ];
    }
}
