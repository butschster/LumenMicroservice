<?php

namespace Butschster\Exchanger\Amqp;

use Closure;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use Throwable;
use Butschster\Exchanger\Contracts\Amqp\Connector as ConnectorContract;
use Butschster\Exchanger\Exceptions\Stop;

/**
 * @internal
 */
class Consumer implements \Butschster\Exchanger\Contracts\Amqp\Consumer
{
    private ConnectorContract $connector;
    private Config $config;
    private int $messageCount = 0;

    public function __construct(Config $config, ConnectorContract $connector)
    {
        $this->connector = $connector;
        $this->config = $config;
    }

    public function consume(array $properties, string $queue, Closure $closure): bool
    {
        $properties['queue'] = $queue;

        $this->connector->connect($properties);
        $result = $this->process($queue, $closure);
        $this->connector->disconnect();

        return $result;
    }

    public function reply(AMQPMessage $message, string $replyTo): void
    {
        $this->connector->getChannel()->basic_publish($message, '', $replyTo);
    }

    public function acknowledge(AMQPMessage $message): void
    {
        $message->getChannel()->basic_ack($message->getDeliveryTag());

        if ($message->body === 'quit') {
            $message->getChannel()->basic_cancel($message->getConsumerTag());
        }
    }

    public function reject(AMQPMessage $message, bool $requeue = false): void
    {
        $message->getChannel()->basic_reject($message->getDeliveryTag(), $requeue);
    }

    private function process(string $queue, Closure $closure): bool
    {
        try {
            $this->messageCount = $this->getQueueMessageCount();

            if (!$this->config->getProperty('persistent') && $this->messageCount == 0) {
                throw new Stop();
            }

            $object = $this;
            $this->connector->getChannel()->basic_qos(null, 1, null);
            $this->connector->getChannel()->basic_consume(
                $queue,
                $this->config->getProperty('consumer_tag'),
                $this->config->getProperty('consumer_no_local'),
                $this->config->getProperty('consumer_no_ack'),
                $this->config->getProperty('consumer_exclusive'),
                $this->config->getProperty('consumer_nowait'),
                function ($message) use ($closure, $object) {
                    $closure($message, $object);
                }
            );

            // consume
            while (count($this->connector->getChannel()->callbacks)) {
                $this->connector->getChannel()->wait(
                    null,
                    !$this->config->getProperty('blocking'),
                    $this->config->getProperty('timeout') ?: 0
                );
            }
        } catch (Throwable $e) {
            if ($e instanceof Stop) {
                return true;
            }

            if ($e instanceof AMQPTimeoutException) {
                return true;
            }

            throw $e;
        }

        return true;
    }

    private function getQueueMessageCount(): int
    {
        if (is_array($info = $this->connector->getQueueInfo())) {
            return $info[1] ?? 0;
        }

        return 0;
    }
}
