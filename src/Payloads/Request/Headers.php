<?php

namespace Butschster\Exchanger\Payloads\Request;

use Butschster\Exchanger\Contracts\Exchange\Payload;
use Butschster\Exchanger\Contracts\Exchange\Request\Token;
use DateTimeInterface;
use JMS\Serializer\Annotation as JMS;

class Headers implements Payload
{
    /** @JMS\Type("string") */
    public ?string $ip = null;

    /** @JMS\Type("string") */
    public ?string $version = null;

    /**
     * Service name that sent this request
     * @JMS\Type("string")
     */
    public ?string $requester = null;

    /** @JMS\Type("Carbon\Carbon") */
    public DateTimeInterface $timestamp;

    /** @JMS\Type("Butschster\Exchanger\Payloads\Request\Meta") */
    public Meta $meta;

    /** @JMS\Type("string") */
    public ?string $token = null;

    public ?Token $tokenInfo = null;
}
