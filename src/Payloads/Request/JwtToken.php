<?php

namespace Butschster\Exchanger\Payloads\Request;

use Butschster\Exchanger\Contracts\Exchange\Request\Token;
use DateTimeInterface;
use JMS\Serializer\Annotation as JMS;

class JwtToken implements Token
{

    /** @JMS\Type("string") */
    public ?string $issuer = null;

    /** @JMS\Type("Carbon\Carbon") */
    public ?DateTimeInterface $expiresAt = null;

    /** @JMS\Type("string") */
    public $userId = null;

    public function issuer(): ?string
    {
        return $this->issuer;
    }

    public function expiresAt(): ?DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function userId()
    {
        return $this->userId;
    }
}
