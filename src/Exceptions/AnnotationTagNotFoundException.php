<?php

namespace Butschster\Exchanger\Exceptions;

use InvalidArgumentException;

class AnnotationTagNotFoundException extends InvalidArgumentException
{
    public function __construct(string $tag)
    {
        parent::__construct(sprintf('Annotation tag not found: %s', $tag));
    }
}
