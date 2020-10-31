<?php

namespace Butschster\Exchanger\Exchange\Request;

use Butschster\Exchanger\Contracts\Exchange\Request\Token;
use Butschster\Exchanger\Contracts\Exchange\Request\TokenDecoder;
use Butschster\Exchanger\Exceptions\InvalidTokenException;
use Butschster\Exchanger\Payloads\Request\JwtToken;
use Carbon\Carbon;
use Firebase\JWT\JWT;

class JWTTokenDecoder implements TokenDecoder
{
    private string $secret;
    private string $algo;

    public function __construct(string $secret, string $algo)
    {
        $this->secret = $secret;
        $this->algo = $algo;
    }

    /** @inheritDoc */
    public function decode(string $token): Token
    {
        try {
            $token = JWT::decode($token, $this->secret, [$this->algo]);

            $info = new JwtToken();
            $info->issuer = $token->iss ?? null;
            $info->expiresAt = isset($token->exp) ? Carbon::createFromTimestamp($token->exp) : null;
            $info->userId = $token->user_id ?? null;

            return $info;
        } catch (\Throwable $e) {
            throw new InvalidTokenException($e->getMessage());
        }
    }
}
