<?php

namespace Butschster\Exchanger\Providers;

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

        $this->publishConfig();
    }

    /**
     * Publish Config
     */
    public function publishConfig(): void
    {
        if ($this->app->runningInConsole()) {
            foreach ($this->configs as $config) {
                $this->publishes([
                    __DIR__ . '/../../config/' . $config . '.php' => config_path($config . '.php'),
                ], 'lumen-microservice');
            }
        }
    }
}
