<?php

namespace Butschster\Exchanger\Events\Route;

use Butschster\Exchanger\Contracts\Exchange\IncomingRequest;
use Butschster\Exchanger\Contracts\Exchange\Route;
use Throwable;

class ExceptionThrown
{
    public Route $route;
    public IncomingRequest $request;
    public Throwable $e;

    public function __construct(Route $route, IncomingRequest $request, Throwable $e)
    {
        $this->route = $route;
        $this->request = $request;
        $this->e = $e;
    }
}
