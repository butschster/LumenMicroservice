<?php

namespace Butschster\Tests\Exchange\Request;

use Butschster\Exchanger\Contracts\Amqp\Message;
use Butschster\Exchanger\Contracts\Exchange\IncomingRequest;
use Butschster\Exchanger\Exceptions\ParameterCannotBeResolvedException;
use Butschster\Exchanger\Exchange\Point\Argument;
use Butschster\Exchanger\Exchange\Request\DependencyResolver;
use Butschster\Tests\TestCase;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;

class DependencyResolverTest extends TestCase
{
    /** @var \Illuminate\Contracts\Container\Container|\Mockery\MockInterface */
    private $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = $this->mockContainer();
    }

    function test_message_should_return_incoming_request()
    {
        $resolver = $this->makeResolver();

        $request = $this->mockExchangeIncomingRequest();
        $dependencies = new Collection([
            $dependency = $this->mock(Argument::class)
        ]);

        $dependency->shouldReceive('is')
            ->once()->with(Message::class, IncomingRequest::class)->andReturnTrue();

        $resolved = $resolver->resolve($request, $dependencies);

        $this->assertTrue(in_array($request, $resolved));
    }

    function test_logger_interface_should_return_logger_object()
    {
        $resolver = $this->makeResolver();

        $request = $this->mockExchangeIncomingRequest();
        $dependencies = new Collection([
            $dependency = $this->mock(Argument::class)
        ]);

        $dependency->shouldReceive('is')
            ->once()->with(Message::class, IncomingRequest::class)->andReturnFalse();

        $dependency->shouldReceive('is')
            ->once()->with(LoggerInterface::class)->andReturnTrue();

        $this->container->shouldReceive('make')->once()->with(LoggerInterface::class)
            ->andReturn('logger');

        $resolved = $resolver->resolve($request, $dependencies);

        $this->assertTrue(in_array('logger', $resolved));
    }

    function test_if_request_has_payload_with_given_key_it_should_be_returned()
    {
        $resolver = $this->makeResolver();

        $request = $this->mockExchangeIncomingRequest();
        $dependencies = new Collection([
            $dependency = $this->mock(Argument::class)
        ]);

        $request->shouldReceive('getPayload')->once()->with('test-key', 'test-class')->andReturn('payload');

        $dependency->shouldReceive('getName')->once()->andReturn('test-key');
        $dependency->shouldReceive('getClass')->once()->andReturn('test-class');

        $dependency->shouldReceive('is')
            ->once()->with(Message::class, IncomingRequest::class)->andReturnFalse();

        $dependency->shouldReceive('is')
            ->once()->with(LoggerInterface::class)->andReturnFalse();

        $resolved = $resolver->resolve($request, $dependencies);

        $this->assertTrue(in_array('payload', $resolved));
    }

    function test_if_dependency_cannot_be_resolved_throw_an_exception()
    {
        $this->expectException(ParameterCannotBeResolvedException::class);
        $this->expectExceptionMessage('Dependency test-key [test-class] cannot be resolved.');

        $resolver = $this->makeResolver();

        $request = $this->mockExchangeIncomingRequest();
        $dependencies = new Collection([
            $dependency = $this->mock(Argument::class)
        ]);

        $request->shouldReceive('getPayload')->once()->with('test-key', 'test-class')->andReturnNull();

        $dependency->shouldReceive('getName')->twice()->andReturn('test-key');
        $dependency->shouldReceive('getClass')->twice()->andReturn('test-class');

        $dependency->shouldReceive('is')
            ->once()->with(Message::class, IncomingRequest::class)->andReturnFalse();

        $dependency->shouldReceive('is')
            ->once()->with(LoggerInterface::class)->andReturnFalse();

        $resolved = $resolver->resolve($request, $dependencies);

        $this->assertTrue(in_array('payload', $resolved));
    }

    function makeResolver(): DependencyResolver
    {
        return new DependencyResolver(
            $this->container
        );
    }
}
