<?php

namespace Butschster\Exchanger\Contracts\Exchange\Request;

use DateTimeInterface;

interface Token
{
    /**
     * The issuer of token (Token Issuer)
     * @return string|null
     */
    public function issuer(): ?string;

    /**
     * Expiry (Time at which the token expires)
     * @return DateTimeInterface|null
     */
    public function expiresAt(): ?DateTimeInterface;

    /**
     * User ID
     * @return mixed
     */
    public function userId();
}
