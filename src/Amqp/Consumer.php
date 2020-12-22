<?php

namespace Butschster\Exchanger\Amqp;

use Butschster\Exchanger\Contracts\Exchange\PayloadFactory;
use Butschster\Exchanger\Contracts\Serializer;
use Closure;
use Illuminate\Contracts\Container\Container;
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
    private Container $container;

    public function __construct(Config $config, Container $container, ConnectorContract $connector)
    {
        $this->connector = $connector;
        $this->config = $config;
        $this->container = $container;
    }

    /** @inheritDoc */
    public function consume(array $properties, string $queue, Closure $closure): bool
    {
        $properties['queue'] = $queue;

        $this->connector->connect($properties);
        $result = $this->process($queue, $closure);
        $this->connector->disconnect();

        return $result;
    }

    /** @inheritDoc */
    public function reply(AMQPMessage $message, ?string $replyTo = null): void
    {
        $this->connector->getChannel()->basic_publish($message, '', (string)$replyTo);
    }

    /** @inheritDoc */
    public function acknowledge(AMQPMessage $message): void
    {
        $message->getChannel()->basic_ack($message->getDeliveryTag());

        if ($message->body === 'quit') {
            $message->getChannel()->basic_cancel($message->getConsumerTag());
        }
    }

    /** @inheritDoc */
    public function reject(AMQPMessage $message, bool $requeue = false): void
    {
        $message->getChannel()->basic_reject($message->getDeliveryTag(), $requeue);
    }

    private function process(string $queue, Closure $callback): bool
    {
        try {
            $this->messageCount = $this->getQueueMessageCount();

            if (!$this->config->getProperty('persistent') && $this->messageCount == 0) {
                throw new Stop();
            }

            // For proper round-robin message distribution with more than one consumer, set the prefetch count to 1.
            // At the price of more overhead in message transfer, messages are evenly distributed over all consumers,
            // requeueing is almost instantly after a consumer fails, and the memory load for both the consumers and
            // RabbitMQ is as small as possible. This is a good approach for message based microservice architectures.
            // Scaling to higher message volumes while preserving quality of service can then be done by adding more consumers.
            $this->connector->getChannel()->basic_qos(null, 1, null);
            $this->connector->getChannel()->basic_consume(
                $queue,
                $this->config->getProperty('consumer_tag'),
                $this->config->getProperty('consumer_no_local'),
                $this->config->getProperty('consumer_no_ack'),
                $this->config->getProperty('consumer_exclusive'),
                $this->config->getProperty('consumer_nowait'),
                function ($message) use ($callback) {
                    $callback($this->makeMessage($message));
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

    private function makeMessage(AMQPMessage $response): Message
    {
        return new Message(
            $this->container->make(PayloadFactory::class),
            $this->container->make(Serializer::class),
            $this,
            $response->body,
            $response->getRoutingKey(),
            $response->has('correlation_id') ? $response->get('correlation_id') : null,
            $response->has('reply_to') ? $response->get('reply_to') : null,
            $response
        );
    }
}
