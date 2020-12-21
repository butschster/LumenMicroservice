<?php

namespace Butschster\Exchanger\Exchange\Middleware;

use Butschster\Exchanger\Contracts\Exchange\IncomingRequest;
use Butschster\Exchanger\Exceptions\InvalidTokenException;
use Closure;

class ValidateTokenMiddleware
{
    /**
     * Check if request contains token
     *
     * @param IncomingRequest $request
     * @param Closure $next
     * @return mixed
     * @throws InvalidTokenException
     *
     */
    public function handle($request, Closure $next)
    {
        if (!$request->getRequestHeaders()->token) {
            throw new InvalidTokenException();
        }

        return $next($request);
    }
}
