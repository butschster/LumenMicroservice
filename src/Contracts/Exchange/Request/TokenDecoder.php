<?php

namespace Butschster\Exchanger\Contracts\Exchange\Request;

use Butschster\Exchanger\Exceptions\InvalidTokenException;

interface TokenDecoder
{
    /**
     * Decode token from string
     * @param string $token
     * @return Token|null
     * @throws InvalidTokenException
     */
    public function decode(string $token): Token;
}
