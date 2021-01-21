<?php

namespace Butschster\Exchanger\Events\Route;

use Butschster\Exchanger\Contracts\Exchange\IncomingRequest;
use Butschster\Exchanger\Contracts\Exchange\Route;

class Dispatched
{
    public Route $route;
    public IncomingRequest $request;

    public function __construct(Route $route, IncomingRequest $request)
    {
        $this->route = $route;
        $this->request = $request;
    }
}
