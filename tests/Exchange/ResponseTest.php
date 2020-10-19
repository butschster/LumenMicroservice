<?php

namespace Butschster\Tests\Exchange;

use Butschster\Exchanger\Exchange\Response;
use Butschster\Exchanger\Payloads\Error;
use Butschster\Exchanger\Payloads\Payload;
use Butschster\Exchanger\Payloads\Request;
use Butschster\Exchanger\Payloads\Response as ResponsePayload;
use Butschster\Tests\TestCase;

class ResponseTest extends TestCase
{
    /** @var \Butschster\Exchanger\Contracts\Serializer|\Mockery\MockInterface */
    private $serializer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serializer = $this->mockSerializer();
    }

    function test_gets_response()
    {
        $this->assertEquals(
            '{hello:world}',
            $this->makeResponse()->getResponse()
        );
    }

    function test_maps_class()
    {
        $this->serializer->shouldReceive('deserialize')
            ->once()->with(
                '{hello:world}',
                ResponsePayload::class,
                [Payload::class => Error::class]
            )->andReturn($response = new ResponsePayload());

        $this->assertEquals(
            $response,
            $this->makeResponse()->mapClass(Error::class)
        );
    }

    function test_maps()
    {
        $this->serializer->shouldReceive('deserialize')
            ->once()->with(
                '{hello:world}',
                ResponsePayload::class,
                [Request::class => Error::class]
            )->andReturn($response = new ResponsePayload());

        $this->assertEquals(
            $response,
            $this->makeResponse()->map([Request::class => Error::class])
        );
    }

    protected function makeResponse(): Response
    {
        return new Response(
            $this->serializer,
            '{hello:world}'
        );
    }
}
