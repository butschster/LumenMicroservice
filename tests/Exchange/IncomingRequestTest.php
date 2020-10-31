<?php

namespace Butschster\Tests\Exchange;

use Butschster\Exchanger\Exchange\IncomingRequest;
use Butschster\Exchanger\Exchange\Request\MessageValidator;
use Butschster\Exchanger\Payloads\Payload;
use Butschster\Exchanger\Payloads\Request;
use Butschster\Exchanger\Payloads\Response;
use Butschster\Tests\TestCase;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Mockery as m;

class IncomingRequestTest extends TestCase
{
    /**  @var \Butschster\Exchanger\Contracts\Exchange\PayloadFactory|\Mockery\MockInterface */
    private $factory;
    /** @var \Illuminate\Contracts\Container\Container|\Mockery\MockInterface */
    private $container;
    /** @var \Butschster\Exchanger\Contracts\Amqp\Message|\Mockery\MockInterface */
    private $message;
    private Response\Headers $responseHeaders;

    protected function setUp(): void
    {
        parent::setUp();

        $this->responseHeaders = new Response\Headers();
        $this->responseHeaders->meta = new Response\Meta();

        $this->factory = $this->mockExchangePayloadFactory();
        $this->container = $this->mockContainer();
        $this->message = $this->mockAmqpMessage();
    }

    function test_sends_response()
    {
        $payload = new Payload();

        $this->message->shouldReceive('reply')
            ->once()->with($payload, [], $this->responseHeaders);

        $this->makeIncomingRequest()->sendResponse($payload);
    }

    function test_sends_empty_response()
    {
        $payload = new Payload();

        $this->factory->shouldReceive('createEmptyPayload')->once()->andReturn($payload);
        $this->message->shouldReceive('reply')
            ->once()->with($payload, [], $this->responseHeaders);

        $this->makeIncomingRequest()->sendEmptyResponse();
    }

    function test_gets_body()
    {
        $this->message->shouldReceive('getBody')->once()->andReturn('{hello: world}');

        $this->assertEquals(
            '{hello: world}',
            $this->makeIncomingRequest()->getBody()
        );
    }

    function test_gets_subject()
    {
        $this->message->shouldReceive('getSubject')->once()->andReturn('com.test');

        $this->assertEquals(
            'com.test',
            $this->makeIncomingRequest()->getSubject()
        );
    }

    function test_reply_on_message()
    {
        $this->message->shouldReceive('reply')
            ->once()->with($payload = new Payload(), [], $headers = new Response\Headers());

        $this->makeIncomingRequest()->reply($payload, [], $headers);
    }

    function test_validate_incoming_rules()
    {
        $validator = $this->mockMessageValidator();
        $this->container->shouldReceive('make')
            ->once()->with(MessageValidator::class)->andReturn($validator);
        $validator->shouldReceive('validate')
            ->once()->with($this->message, $rules = ['key' => 'required']);

        $this->makeIncomingRequest()->validate($rules);
    }

    function test_add_pagination_headers_to_response()
    {
        $paginator = m::mock(LengthAwarePaginator::class);

        $paginator->shouldReceive('total')->once()->andReturn(10);
        $paginator->shouldReceive('perPage')->once()->andReturn(25);
        $paginator->shouldReceive('currentPage')->once()->andReturn(2);
        $paginator->shouldReceive('lastPage')->once()->andReturn(100);

        $this->assertNull($this->responseHeaders->meta->pagination);

        $this->makeIncomingRequest()->withPagination($paginator);

        $this->assertEquals(10, $this->responseHeaders->meta->pagination->total);
        $this->assertEquals(25, $this->responseHeaders->meta->pagination->perPage);
        $this->assertEquals(2, $this->responseHeaders->meta->pagination->currentPage);
        $this->assertEquals(100, $this->responseHeaders->meta->pagination->totalPages);
    }

    function test_gets_payload()
    {
        $this->message->shouldReceive('getPayload')
            ->once()->with('foo', null)->andReturn($payload = new Payload());

        $this->assertEquals(
            $payload,
            $this->makeIncomingRequest()->getPayload('foo')
        );
    }

    function test_gets_request_headers_with_null()
    {
        $this->assertNull($this->makeIncomingRequest()->getRequestHeaders());
    }

    function test_gets_request_headers()
    {
        $headers = new Request\Headers();

        $this->assertEquals(
            $headers,
            $this->makeIncomingRequest($headers)->getRequestHeaders()
        );
    }

    protected function makeIncomingRequest(?Request\Headers $requestHeaders = null): IncomingRequest
    {
        $this->factory->shouldReceive('createResponseHeaders')->once()->andReturn($this->responseHeaders);

        return new IncomingRequest(
            $this->factory,
            $this->container,
            $this->message,
            $requestHeaders
        );
    }
}
