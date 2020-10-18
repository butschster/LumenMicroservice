<?php

namespace Butschster\Exchanger\Exchange;

use Illuminate\Contracts\Config\Repository;

class Config
{
    private Repository $config;

    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    public function name(): string
    {
        return $this->config->get('microservice.name');
    }

    public function version(): string
    {
        return $this->config->get('microservice.version');
    }
}
