<?php

namespace Butschster\Tests\Exchange\Request;

use Butschster\Exchanger\Exceptions\InvalidTokenException;
use Butschster\Exchanger\Exchange\Request\JWTTokenDecoder;
use Butschster\Tests\TestCase;
use Carbon\Carbon;
use Firebase\JWT\JWT;

class JWTTokenDecoderTest extends TestCase
{
    /**
     * @dataProvider tokenPayloadDataProvider
     */
    function test_decode(array $payload, ?string $issuer = null, ?string $userId = null, ?string $date = null)
    {
        $jwt = JWT::encode($payload, 'secret', 'HS256');
        $token = $this->makeTokenDecoder()->decode($jwt);

        $this->assertEquals($issuer, $token->issuer());
        $this->assertEquals($userId, $token->userId());

        if ($date) {
            $this->assertEquals($date, $token->expiresAt()->format('Y-m-d H:i:s'));
        } else {
            $this->assertNull($token->expiresAt());
        }
    }

    /**
     * @dataProvider invalidTokensDataProvider
     */
    function test_invalid_token_should_throw_an_exception(string $token, string $message)
    {
        $this->expectExceptionMessage(InvalidTokenException::class);
        $this->expectExceptionMessage($message);

        $this->makeTokenDecoder()->decode($token);
    }

    public function makeTokenDecoder(): JWTTokenDecoder
    {
        return new JWTTokenDecoder('secret', 'HS256');
    }

    public function tokenPayloadDataProvider()
    {
        $expAt = Carbon::now()->addDay();

        return [
            [
                [
                    "iss" => "http://example.org",
                    'user_id' => 'test-id',
                    'exp' => $expAt->getTimestamp()
                ],
                "http://example.org",
                'test-id',
                $expAt->format('Y-m-d H:i:s')
            ],
            [
                [
                    "iss" => "http://example.org",
                    'user_id' => 'test-id'
                ],
                "http://example.org",
                'test-id',
            ],
            [
                [],
            ]
        ];
    }

    public function invalidTokensDataProvider()
    {
        return [
            [
                'test',
                'Wrong number of segments',
            ],
            [
                JWT::encode([
                    "iss" => "http://example.org",
                    'user_id' => 'test-id',
                    'exp' => Carbon::now()->subMinute()
                ], 'secret', 'HS256'),
                'Expired token'
            ],
            [
                'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9leGFtcGxlLm9yZyIsInVzZXJfaWQiOiJ0ZXN0LWlkIiwiZXhwIjoiMjAyMC0xMC0yMFQxMDoyOToxMi4wMzU0NzNaIn0.QiFbgqT0jOfKcM6m4wdyghrq5_WD8QkvE728CVkKyP',
                'Signature verification failed'
            ]
        ];
    }
}
