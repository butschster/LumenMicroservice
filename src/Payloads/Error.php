<?php

namespace Butschster\Exchanger\Payloads;

use JMS\Serializer\Annotation as JMS;

class Error implements \Butschster\Exchanger\Contracts\Exchange\Payload
{
    /**
     * @JMS\Type("int")
     */
    public int $code;

    /**
     * @JMS\Type("string")
     */
    public string $message;

    /**
     * @JMS\Type("array")
     */
    public array $data = [];

    /**
     * @JMS\Type("array<Butschster\Exchanger\Payloads\Error\Trace>")
     */
    public array $trace = [];
}
