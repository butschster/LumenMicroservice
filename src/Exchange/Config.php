<?php

namespace Butschster\Exchanger\Exchange;

use Butschster\Exchanger\Contracts\Exchange\Config as ConfigContract;
use Illuminate\Contracts\Config\Repository;

class Config implements ConfigContract
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
