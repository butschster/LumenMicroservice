<?php

namespace Butschster\Exchanger\Console\Commands;

use Illuminate\Console\Command;
use Butschster\Exchanger\Contracts\Exchange\Point;
use Butschster\Exchanger\Contracts\ExchangeManager;

class RunMicroservice extends Command
{
    protected $signature = 'service:run';
    protected $description = 'Microservice runner';

    public function handle(ExchangeManager $exchange, Point $service)
    {
        $exchange->register(
            $service
        );
    }
}
