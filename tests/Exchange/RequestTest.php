<?php

namespace Butschster\Tests\Exchange;

use Butschster\Exchanger\Contracts\Exchange\Payload;
use Butschster\Exchanger\Exchange\Request;
use Butschster\Exchanger\Payloads\Response as ResponsePayload;
use Butschster\Tests\TestCase;

class RequestTest extends TestCase
{
    /** @var \Butschster\Exchanger\Contracts\Exchange\PayloadFactory|\Mockery\MockInterface */
    private $factory;
    /** @var \Butschster\Exchanger\Contracts\Exchange\Client|\Mockery\MockInterface */
    private $client;
    /** @var \Butschster\Exchanger\Contracts\Serializer|\Mockery\MockInterface */
    private $serializer;
    private \Butschster\Exchanger\Payloads\Request $requestPayload;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = $this->mockExchangePayloadFactory();
        $this->client = $this->mockExchangeClient();
        $this->serializer = $this->mockSerializer();
        $this->requestPayload = new \Butschster\Exchanger\Payloads\Request();
    }

    function test_gets_subject()
    {
        $this->assertEquals(
            'com.test',
            $this->makeRequest()->getSubject()
        );
    }

    function test_gets_payload()
    {
        $this->assertEquals(
            $this->requestPayload,
            $this->makeRequest()->getPayload()
        );
    }

    function test_send_request()
    {
        $responseClass = \Butschster\Exchanger\Payloads\Error::class;

        $this->serializer->shouldReceive('serialize')
            ->once()->with($this->requestPayload)->andReturn('{hello:world}');

        $this->serializer->shouldReceive('deserialize')
            ->once()->with(
                '{foo:bar}',
                ResponsePayload::class,
                [\Butschster\Exchanger\Payloads\Payload::class => $responseClass]
            )
            ->andReturn($responsePayload = new ResponsePayload());

        $this->client->shouldReceive('request')
            ->once()->with('com.test', '{hello:world}', true)->andReturn('{foo:bar}');

        $this->assertEquals(
            $responsePayload,
            $this->makeRequest()->send($responseClass)
        );
    }

    function test_broadcast()
    {
        $this->serializer->shouldReceive('serialize')
            ->once()->with($this->requestPayload)->andReturn('{hello:world}');

        $this->client->shouldReceive('broadcast')
            ->once()->with('com.test', '{hello:world}', false);

        $this->makeRequest()->broadcast();
    }

    public function makeRequest(?Payload $payload = null): Request
    {
        $this->factory->shouldReceive('createRequest')
            ->once()->with($payload)->andReturn($this->requestPayload);

        return new Request(
            $this->factory,
            $this->serializer,
            $this->client,
            'com.test',
            $payload
        );
    }
}
