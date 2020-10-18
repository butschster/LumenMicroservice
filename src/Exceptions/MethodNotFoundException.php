<?php

namespace Butschster\Exchanger\Exceptions;

use RuntimeException;

class MethodNotFoundException extends RuntimeException
{
    /**
     * @param string $subject
     */
    public function __construct(string $subject)
    {
        parent::__construct(sprintf('Method with subject was not found: %s', $subject));
    }
}
