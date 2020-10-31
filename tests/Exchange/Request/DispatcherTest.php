<?php

namespace Butschster\Tests\Exchange\Request;

use Butschster\Exchanger\Contracts\Exchange\IncomingRequest;
use Butschster\Exchanger\Contracts\Exchange\Request\Token;
use Butschster\Exchanger\Contracts\Exchange\Request\TokenDecoder;
use Butschster\Exchanger\Exceptions\Handler;
use Butschster\Exchanger\Exchange\Request\DependencyResolver;
use Butschster\Exchanger\Exchange\Request\Dispatcher;
use Butschster\Exchanger\Payloads\Request\Headers;
use Butschster\Tests\TestCase;
use Exception;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;

class DispatcherTest extends TestCase
{
    private \Mockery\MockInterface $exceptionsHandler;
    /** @var \Illuminate\Contracts\Container\Container|\Mockery\MockInterface */
    private $container;
    /** @var \Butschster\Exchanger\Contracts\Serializer|\Mockery\MockInterface */
    private $serializer;
    private \Mockery\MockInterface $pipeline;
    private \Mockery\MockInterface $dependencyResolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->exceptionsHandler = $this->mock(Handler::class);
        $this->container = $this->mockContainer();
        $this->serializer = $this->mockSerializer();
        $this->pipeline = $this->mock(Pipeline::class);
        $this->dependencyResolver = $this->mock(DependencyResolver::class);
    }

    function test_dispatch()
    {
        $payload = json_decode('{"hello":"world","headers":{"ip":"127.0.0.1"}}');
        $headers = new Headers();

        $this->assertDispatcherData($payload, $headers);
    }

    function test_if_request_contains_token_in_header_it_should_be_decoded()
    {
        $payload = json_decode('{"hello":"world","headers":{"ip":"127.0.0.1","token":"test-token"}}');

        $headers = new Headers();
        $headers->token = 'test-token';

        $this->container->shouldReceive('make')
            ->once()->with(TokenDecoder::class)->andReturn($decoder = $this->mockExchangeRequestTokenDecoder());

        $decoder->shouldReceive('decode')
            ->once()->with('test-token')->andReturn($this->mock(Token::class));

        $this->assertDispatcherData($payload, $headers);
    }

    function test_dispatch_should_handle_exception_if_it_was_thrown()
    {
        $headers = new Headers();

        $message = $this->mockAmqpMessage();
        $message->shouldReceive('getPayload')
            ->once()->with('headers', Headers::class)->andReturn($headers);

        $this->container->shouldReceive('make')->once()->with(
                IncomingRequest::class,
                [
                    'message' => $message,
                    'headers' => $headers
                ]
            )->andReturn($request = $this->mockExchangeIncomingRequest());


        $route = $this->mockExchangeRoute();

        $exception = new Exception('Something went wrong');
        $route->shouldReceive('getArguments')->once()->andReturnUsing(function () use($exception) {
            throw $exception;
        });

        $this->exceptionsHandler->shouldReceive('report')->once()->with($exception);
        $this->exceptionsHandler->shouldReceive('render')->once()->with($request, $exception);

        $this->makeDispatcher()->dispatch($message, $route);
    }

    protected function makeDispatcher(): Dispatcher
    {
        return new Dispatcher(
            $this->exceptionsHandler,
            $this->container,
            $this->serializer,
            $this->pipeline,
            $this->dependencyResolver
        );
    }

    private function assertDispatcherData($payload, Headers $headers): void
    {
        $args = new Collection();
        $dependencies = ['foo', 'bar'];
        $request = $this->mockExchangeIncomingRequest();

        $route = $this->mockExchangeRoute();
        $route->shouldReceive('getArguments')->once()->andReturn($args);
        $route->shouldReceive('getMiddleware')->once()->andReturn([]);
        $route->shouldReceive('call')->once()->with($dependencies);

        $message = $this->mockAmqpMessage();
        $message->shouldReceive('getPayload')
            ->once()->with('headers', Headers::class)
            ->andReturn($headers);

        $this->container->shouldReceive('make')
            ->once()->with(
                IncomingRequest::class,
                [
                    'message' => $message,
                    'headers' => $headers
                ]
            )->andReturn($request);


        $this->dependencyResolver->shouldReceive('resolve')->once()
            ->with($request, $args)->andReturn($dependencies);

        $this->makeDispatcher()->dispatch($message, $route);
    }
}
