<?php

namespace Butschster\Exchanger\Exceptions;

use Butschster\Exchanger\Exchange\Point\Argument;
use RuntimeException;

class ParameterCannotBeResolvedException extends RuntimeException
{
    public function __construct(Argument $dependency)
    {
        parent::__construct(
            sprintf('Dependency %s [%s] cannot be resolved.', $dependency->getName(), $dependency->getClass())
        );
    }
}
