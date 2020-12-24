<?php

namespace Butschster\Exchanger\Amqp;

use Illuminate\Contracts\Container\Container;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use Butschster\Exchanger\Contracts\Amqp;
use Butschster\Exchanger\Contracts\Exchange\Client as ClientContract;
use Butschster\Exchanger\Contracts\Exchange\Point;
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

    /** @inheritDoc */
    public function subscribe(Point $exchange, callable $handler): void
    {
        $pointInfo = $this->container->make(Parser::class)->parse($exchange);

        $properties = $this->properties + [
                'routing' => $pointInfo->getRouteSubjects(),
                'timeout' => 0,
            ];

        $handler($pointInfo);

        $this->consumer->consume(
            $properties,
            $exchange->getName(),
            function (Amqp\Message $response) use ($pointInfo) {
                $this->dispatcher->dispatch(
                    $response,
                    $pointInfo->getRoute($response->getSubject())
                );
            });
    }

    /** @inheritDoc */
    public function request(string $subject, string $payload, bool $persistent = true): string
    {
        $response = '{}';

        $this->requester->request(
            $this->properties,
            $subject,
            $payload,
            function ($payload) use (&$response) {
                $response = $payload->body;
            },
            $persistent
        );

        return $response;
    }

    /** @inheritDoc */
    public function deferredRequest(LoopInterface $loop, string $subject, string $payload, bool $persistent = true): PromiseInterface
    {
        $deferred = new Deferred();

        $this->requester->deferredRequest(
            $this->properties,
            $loop,
            $subject,
            $payload,
            $persistent
        )->then(function ($response) use ($deferred) {
            $deferred->resolve($response->body);
        }, function ($exception) use ($deferred) {
            $deferred->reject($exception);
        });

        return $deferred->promise();
    }

    /** @inheritDoc */
    public function broadcast(string $subject, string $payload, bool $persistent = true): void
    {
        $this->publisher->publish(
            $this->properties,
            $subject,
            $payload,
            $persistent
        );
    }

    /** @inheritDoc */
    public function setProperty(string $property, $value): void
    {
        $this->properties[$property] = $value;
    }
}
