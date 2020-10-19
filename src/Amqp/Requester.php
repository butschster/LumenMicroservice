<?php

namespace Butschster\Exchanger\Amqp;

use Carbon\Carbon;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use Butschster\Exchanger\Contracts\Amqp\Connector as ConnectorContract;
use Butschster\Exchanger\Contracts\Amqp\Requester as RequesterContract;

/**
 * @internal
 */
class Requester implements RequesterContract
{
    private ConnectorContract $connector;
    private Config $config;
    private bool $hasResponse = false;
    protected ?array $queueInfo = null;

    public function __construct(Config $config, ConnectorContract $connector)
    {
        $this->connector = $connector;
        $this->config = $config;

        $this->connector->afterConnect(function (AMQPChannel $channel) {
            $this->queueInfo = $channel->queue_declare('', false, false, true, false);
        });
    }

    public function request(array $properties, string $route, string $message, callable $callback, int $deliveryMode = AMQPMessage::DELIVERY_MODE_PERSISTENT): void
    {
        $properties['routing'] = $route;
        $properties['nobinding'] = true;

        $this->connector->connect($properties);

        $correlationId = uniqid();

        $message = new AMQPMessage($message, [
            'content_type' => 'application/json',
            'delivery_mode' => $deliveryMode,
            'correlation_id' => $correlationId,
            'reply_to' => $this->getQueueInfo(),
            'expiration' => '30000',
        ]);

        $this->consume($correlationId, $callback);
        $this->publish($route, $message);
        $this->waitConsume();

        $this->connector->disconnect();
    }

    public function deferredRequest(array $properties, LoopInterface $loop, string $route, string $message, int $deliveryMode = AMQPMessage::DELIVERY_MODE_PERSISTENT): PromiseInterface
    {
        $properties['routing'] = $route;
        $properties['nobinding'] = true;

        $this->connector->connect($properties);

        $correlationId = uniqid();

        $message = new AMQPMessage($message, [
            'content_type' => 'application/json',
            'delivery_mode' => $deliveryMode,
            'correlation_id' => $correlationId,
            'reply_to' => $this->getQueueInfo(),
            'expiration' => '30000',
        ]);

        $this->publish($route, $message);
        $deferred = new Deferred();

        $loop->futureTick(
            $this->listener($deferred, $loop, $correlationId)
        );

        return $deferred->promise();
    }

    private function consume(string $correlationId, callable $callback): void
    {
        $this->connector->getChannel()
            ->basic_consume($this->getQueueInfo(), '', false, false, false, false, function ($message) use ($correlationId, $callback) {
                if ($message->get('correlation_id') == $correlationId) {
                    $this->hasResponse = true;
                    $callback($message);
                }
            });
    }

    private function publish(string $route, AMQPMessage $message): void
    {
        $this->connector->getChannel()
            ->basic_publish($message, $this->connector->getExchange(), $route);
    }

    private function waitConsume(): void
    {
        while (!$this->hasResponse) {
            $this->connector->getChannel()
                ->wait(null, false, $this->config->getProperty('timeout') ?: 0);
        }
    }

    private function listener(Deferred $deferred, LoopInterface $loop, string $correlationId): callable
    {
        $timeoutTime = Carbon::now()->addSeconds($this->config->getProperty('timeout'));

        return function () use ($deferred, $loop, $timeoutTime, $correlationId) {
            $message = $this->getMessage($correlationId);

            if (!is_null($message)) {
                $this->connector->disconnect();
                $deferred->resolve($message);

                return;
            }

            if ($timeoutTime->isPast()) {
                $deferred->reject(new AMQPTimeoutException("Timeout waiting on channel"));
            }

            $loop->futureTick($this->listener($deferred, $loop, $correlationId));
        };
    }

    private function getMessage(string $correlationId): ?AMQPMessage
    {
        $message = $this->connector->getChannel()->basic_get($this->getQueueInfo());

        if (!is_null($message) && $message->get('correlation_id') == $correlationId) {
            $this->connector->getChannel()->basic_ack($message->get('delivery_tag'));

            return $message;
        }
    }

    private function getQueueInfo(): ?string
    {
        return $this->queueInfo[0] ?? null;
    }
}
