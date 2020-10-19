<?php

namespace Butschster\Exchanger\Exceptions;

use RuntimeException;

class RouteNotFoundException extends RuntimeException
{
    /**
     * @param string $subject
     */
    public function __construct(string $subject)
    {
        parent::__construct(sprintf('Route with subject [%s] is not found.', $subject));
    }
}
