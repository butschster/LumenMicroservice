<?php

namespace Butschster\Exchanger\Payloads\Request;

use Butschster\Exchanger\Contracts\Exchange\Request\Token;
use DateTimeInterface;

class JwtToken implements Token
{
    public ?string $issuer = null;
    public ?DateTimeInterface $expiresAt = null;
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
