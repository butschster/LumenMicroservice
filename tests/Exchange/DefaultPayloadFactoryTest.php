<?php

namespace Butschster\Tests\Exchange;

use Butschster\Exchanger\Exchange\DefaultPayloadFactory;
use Butschster\Exchanger\Payloads\Error;
use Butschster\Exchanger\Payloads\Payload;
use Butschster\Exchanger\Payloads\Request;
use Butschster\Exchanger\Payloads\Response;
use Butschster\Tests\TestCase;
use Carbon\Carbon;

class DefaultPayloadFactoryTest extends TestCase
{
    /**
     * @var \Butschster\Exchanger\Contracts\ExchangeManager|\Mockery\MockInterface
     */
    private $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = $this->mockExchangeManager();
    }

    function test_creates_empty_payload()
    {
        $payload = $this->makeFactory()->createEmptyPayload();

        $this->assertInstanceOf(Payload::class, $payload);
    }

    function test_creates_request_without_payload()
    {
        $this->manager->shouldReceive('getVersion')->once()->andReturn('1.0');
        $this->manager->shouldReceive('getName')->once()->andReturn('test');

        $payload = $this->makeFactory()->createRequest();

        $this->assertInstanceOf(Payload::class, $payload->payload);
        $this->assertRequestHeadersPayload($payload->headers);
    }

    function test_creates_request_with_payload()
    {
        $this->manager->shouldReceive('getVersion')->once()->andReturn('1.0');
        $this->manager->shouldReceive('getName')->once()->andReturn('test');

        $payload = $this->makeFactory()->createRequest($body = new Payload());

        $this->assertEquals($body, $payload->payload);
        $this->assertRequestHeadersPayload($payload->headers);
    }

    function test_creates_request_headers()
    {
        $this->manager->shouldReceive('getVersion')->once()->andReturn('1.0');
        $this->manager->shouldReceive('getName')->once()->andReturn('test');

        $payload = $this->makeFactory()->createRequestHeaders();
        $this->assertRequestHeadersPayload($payload);
    }

    function test_creates_success_response_without_body_and_headers()
    {
        $payload = $this->makeFactory()->createResponse();

        $this->assertInstanceOf(Response::class, $payload);
        $this->assertTrue($payload->success);
        $this->assertInstanceOf(Response\Headers::class, $payload->headers);
        $this->assertNull($payload->payload);
        $this->assertCount(0, $payload->errors);
    }

    function test_creates_success_response_with_body_and_without_headers()
    {
        $payload = $this->makeFactory()->createResponse($body = new Payload());

        $this->assertInstanceOf(Response::class, $payload);
        $this->assertTrue($payload->success);
        $this->assertInstanceOf(Response\Headers::class, $payload->headers);
        $this->assertEquals($body, $payload->payload);
        $this->assertCount(0, $payload->errors);
    }

    function test_creates_success_response_with_body_and_wit_headers()
    {
        $payload = $this->makeFactory()
            ->createResponse($body = new Payload(), [], $headers = new Response\Headers());

        $this->assertInstanceOf(Response::class, $payload);
        $this->assertTrue($payload->success);
        $this->assertEquals($headers, $payload->headers);
        $this->assertEquals($body, $payload->payload);
        $this->assertCount(0, $payload->errors);
    }

    function test_creates_unsuccessful_response()
    {
        $payload = $this->makeFactory()
            ->createResponse($body = new Payload(), [$error = new Error()], $headers = new Response\Headers());

        $this->assertInstanceOf(Response::class, $payload);
        $this->assertFalse($payload->success);
        $this->assertEquals($headers, $payload->headers);
        $this->assertEquals($body, $payload->payload);
        $this->assertCount(1, $payload->errors);
        $this->assertContains($error, $payload->errors);
    }

    function test_creates_response_headers()
    {
        $payload = $this->makeFactory()->createResponseHeaders();

        $this->assertInstanceOf(Response\Headers::class, $payload);
        $this->assertInstanceOf(Response\Meta::class, $payload->meta);
    }

    protected function makeFactory(): DefaultPayloadFactory
    {
        return new DefaultPayloadFactory($this->manager);
    }

    private function assertRequestHeadersPayload(Request\Headers $headers)
    {
        $this->assertInstanceOf(Request\Headers::class, $headers);
        $this->assertInstanceOf(Request\Meta::class, $headers->meta);
        $this->assertEquals('1.0', $headers->version);
        $this->assertEquals('test', $headers->requester);
        $this->assertEquals(Carbon::now()->getTimestamp(), $headers->timestamp->getTimestamp());
    }
}
