<?php

namespace Butschster\Exchanger\Providers;

use Butschster\Exchanger\Console\Commands\RunMicroservice;

class LaravelServiceProvider extends ExchangeServiceProvider
{
    protected array $configs = ['amqp', 'microservice', 'serializer'];

    /**
     * Bootstrap any application services.
     * @return void
     */
    public function boot(): void
    {
        foreach ($this->configs as $config) {
            $this->mergeConfigFrom(__DIR__ . '/../../config/' . $config . '.php', $config);
        }

        if ($this->app->runningInConsole()) {
            $this->publishConfig();

            $this->commands([
                RunMicroservice::class
            ]);
        }
    }

    /**
     * Publish Config
     */
    public function publishConfig(): void
    {
        foreach ($this->configs as $config) {
            $this->publishes([
                __DIR__ . '/../../config/' . $config . '.php' => config_path($config . '.php'),
            ], 'lumen-microservice');
        }
    }
}
