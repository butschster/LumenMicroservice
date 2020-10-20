<?php

namespace Butschster\Tests\Amqp;

use Butschster\Exchanger\Amqp\Client;
use Butschster\Exchanger\Exchange\Point\Information;
use Butschster\Exchanger\Exchange\Point\Parser;
use Butschster\Exchanger\Exchange\Point\Subject;
use Butschster\Tests\TestCase;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;

class ClientTest extends TestCase
{
    private \Mockery\MockInterface $dispatcher;
    /** @var \Butschster\Exchanger\Contracts\Amqp\Publisher|\Mockery\MockInterface */
    private $publisher;
    /** @var \Butschster\Exchanger\Contracts\Amqp\Consumer|\Mockery\MockInterface */
    private $consumer;
    /** @var \Butschster\Exchanger\Contracts\Amqp\Requester|\Mockery\MockInterface */
    private $requester;
    /** @var \Illuminate\Contracts\Container\Container|\Mockery\MockInterface */
    private $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dispatcher = $this->mockExchangeRequestDispatcher();
        $this->publisher = $this->mockAmqpPublisher();
        $this->consumer = $this->mockAmqpConsumer();
        $this->requester = $this->mockAmqpRequester();
        $this->container = $this->mockContainer();
    }

    function test_subscribes()
    {
        $information = $this->mock(Information::class);
        $information->shouldReceive('getRouteSubjects')
            ->once()->andReturn($subjects = ['com.test', 'com.test2']);
        $information->shouldReceive('getRoute')
            ->once()->with('com.test.action.name')->andReturn($subject = $this->mock(Subject::class));

        $point = $this->mockExchangePoint();
        $point->shouldReceive('getName')->once()->andReturn('com.point');

        $parser = $this->mock(Parser::class);
        $parser->shouldReceive('parse')->once()->with($point)->andReturn($information);

        $this->container->shouldReceive('make')
            ->once()->with(Parser::class)->andReturn($parser);

        $message = $this->mockAmqpMessage();
        $message->shouldReceive('getSubject')->once()->andReturn('com.test.action.name');

        $this->dispatcher->shouldReceive('dispatch')
            ->once()->with($message, $subject);

        $this->consumer->shouldReceive('consume')
            ->once()->withArgs(function ($params, $pointName, callable $callback) use ($subjects, $message) {
                $callback($message);

                return $params === ['routing' => $subjects, 'timeout' => 0,]
                    && $pointName === 'com.point';
            });

        $this->makeClient()->subscribe($point, function (Information $i) use ($information) {
            $this->assertEquals($information, $i);
        });
    }

    /**
     * @dataProvider persistenceDataProvider
     */
    function test_request(bool $persistent)
    {
        $this->requester->shouldReceive('request')->once()->withArgs(function ($props, $subject, $payload, $callback, $p) use($persistent) {

            $callback(json_decode('{"body": "test"}'));

            return $props === []
                && $subject === 'com.test'
                && $payload === '{"foo":"bar"}'
                && $p === $persistent;
        });

        $response = $this->makeClient()->request('com.test', '{"foo":"bar"}', $persistent);

        $this->assertEquals('test', $response);
    }

    /**
     * @dataProvider persistenceDataProvider
     */
    function test_deferred_request_resolved(bool $persistent)
    {
        $loop = $this->mock(LoopInterface::class);

        $promise = $this->mock(PromiseInterface::class);

        $promise->shouldReceive('then')->once()->withArgs(function ($resolve, $reject) {
            $resolve(json_decode('{"body": "test"}'));
            return true;
        });

        $this->requester->shouldReceive('deferredRequest')
            ->once()->with([], $loop, 'com.test', '{"foo":"bar"}', $persistent)->andReturn($promise);

        $response = $this->makeClient()->deferredRequest($loop, 'com.test', '{"foo":"bar"}', $persistent);

        $response->then(function ($body) {
            $this->assertEquals('test', $body);
        });
    }

    /**
     * @dataProvider persistenceDataProvider
     */
    function test_deferred_request_reject(bool $persistent)
    {
        $loop = $this->mock(LoopInterface::class);

        $promise = $this->mock(PromiseInterface::class);

        $promise->shouldReceive('then')->once()->withArgs(function ($resolve, $reject) {
            $reject(json_decode('{"body": "test"}'));
            return true;
        });

        $this->requester->shouldReceive('deferredRequest')
            ->once()->with([], $loop, 'com.test', '{"foo":"bar"}', $persistent)->andReturn($promise);

        $response = $this->makeClient()->deferredRequest($loop, 'com.test', '{"foo":"bar"}', $persistent);

        $response->then(function () {
            $this->fail('should not be executed');
        }, function ($body) {
            $this->assertEquals('test', $body);
        });
    }

    /**
     * @dataProvider persistenceDataProvider
     */
    function test_broadcast(bool $persistent)
    {
        $this->publisher->shouldReceive('publish')->once()->with([], 'com.test', '{"foo":"bar"}', $persistent);
        $this->makeClient()->broadcast('com.test', '{"foo":"bar"}', $persistent);
    }

    protected function makeClient(): Client
    {
        return new Client(
            $this->container,
            $this->publisher,
            $this->consumer,
            $this->requester,
            $this->dispatcher
        );
    }

    public function persistenceDataProvider()
    {
        return [
            [true, false]
        ];
    }
}
