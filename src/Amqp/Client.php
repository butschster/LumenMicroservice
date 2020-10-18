<?php

namespace Butschster\Exchanger\Amqp;

use Illuminate\Contracts\Container\Container;
use PhpAmqpLib\Message\AMQPMessage;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use Butschster\Exchanger\Contracts\Amqp;
use Butschster\Exchanger\Contracts\Exchange\Client as ClientContract;
use Butschster\Exchanger\Contracts\Exchange\PayloadFactory;
use Butschster\Exchanger\Contracts\Exchange\Point;
use Butschster\Exchanger\Contracts\Serializer;
use Butschster\Exchanger\Exchange\Point\Parser;
use Butschster\Exchanger\Exchange\Request\Dispatcher;

/**
 * @internal
 */
class Client implements ClientContract
{
    private Amqp\Publisher $publisher;
    private Amqp\Consumer $consumer;
    private Amqp\Requester $requester;
    protected array $properties = [];
    private Dispatcher $dispatcher;
    private Container $container;

    public function __construct(
        Container $container,
        Amqp\Publisher $publisher,
        Amqp\Consumer $consumer,
        Amqp\Requester $requester,
        Dispatcher $dispatcher
    )
    {
        $this->dispatcher = $dispatcher;
        $this->publisher = $publisher;
        $this->consumer = $consumer;
        $this->requester = $requester;
        $this->container = $container;
    }

    public function subscribe(Point $exchange, callable $handler): void
    {
        $pointInfo = (new Parser())->parse($exchange);

        $properties = $this->properties + [
                'routing' => $pointInfo->getRouteSubjects(),
                'timeout' => 0,
            ];

        $handler($pointInfo);

        $this->consumer->consume(
            $properties,
            $exchange->getName(),
            function (AMQPMessage $response, Amqp\Consumer $consumer) use ($pointInfo) {
                $this->dispatcher->dispatch(
                    $this->makeMessage($consumer, $response),
                    $pointInfo->getRoute($response->getRoutingKey())
                );
            });
    }

    public function request(string $subject, string $payload): string
    {
        $response = null;

        $this->requester->request(
            $this->properties,
            $subject, $payload,
            function ($payload) use (&$response) {
                $response = $payload->body;
            }
        );

        return $response;
    }

    public function deferredRequest(LoopInterface $loop, string $subject, string $payload): PromiseInterface
    {
        $deferred = new Deferred();

        $this->requester->deferredRequest(
            $this->properties,
            $loop,
            $subject,
            $payload
        )->then(function ($response) use ($deferred) {
            $deferred->resolve($response->body);
        }, function ($exception) use ($deferred) {
            $deferred->reject($exception);
        });

        return $deferred->promise();
    }

    public function call(string $subject, string $payload): void
    {
        $this->publisher->publish(
            $this->properties,
            $subject,
            $payload
        );
    }

    public function setProperty(string $property, $value): void
    {
        $this->properties[$property] = $value;
    }

    public function makeMessage(Amqp\Consumer $consumer, AMQPMessage $response): Message
    {
        return new Message(
            $this->container->make(PayloadFactory::class),
            $this->container->make(Serializer::class),
            $consumer,
            $response->body,
            $response->getRoutingKey(),
            $response->has('correlation_id') ? $response->get('correlation_id') : null,
            $response->has('reply_to') ? $response->get('reply_to') : null,
            $response
        );
    }
}
