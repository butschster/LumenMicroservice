<?php

namespace Butschster\Exchanger\Payloads\Error;

use JMS\Serializer\Annotation as JMS;

class Trace
{
    /**
     * @JMS\Type("string")
     */
    public string $file;

    /**
     * @JMS\Type("integer")
     */
    public int $line;

    /**
     * @JMS\Type("string")
     */
    public string $function;

    /**
     * @JMS\Type("string")
     */
    public string $class;
}
