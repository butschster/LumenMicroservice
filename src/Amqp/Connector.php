<?php

namespace Butschster\Exchanger\Amqp;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Butschster\Exchanger\Contracts\Amqp\Connector as ConnectorContract;
use Butschster\Exchanger\Exceptions\ConfigurationException;

/**
 * @internal
 */
class Connector implements ConnectorContract
{
    protected AMQPStreamConnection $connection;
    protected AMQPChannel $channel;
    protected Config $config;
    protected ?array $queueInfo = null;
    protected string $exchange;
    private array $afterCallbacks = [];

    public function __construct(Config $config, AMQPStreamConnection $connection)
    {
        $this->config = $config;
        $this->connection = $connection;
        $this->channel = $connection->channel();
    }

    public function afterConnect(callable $handler): void
    {
        $this->afterCallbacks[] = $handler;
    }

    /** @inheritDoc */
    public function connect(array $properties = []): void
    {
        $this->config->mergeProperties($properties);

        $exchange = $this->config->getProperty('exchange');

        if (empty($exchange)) {
            throw new ConfigurationException('Please check your settings, exchange is not defined.');
        }

        $this->exchange = $exchange;

        $this->channel->exchange_declare(
            $exchange,
            $this->config->getProperty('exchange_type'),
            $this->config->getProperty('exchange_passive'),
            $this->config->getProperty('exchange_durable'),
            $this->config->getProperty('exchange_auto_delete'),
            $this->config->getProperty('exchange_internal'),
            $this->config->getProperty('exchange_nowait'),
            $this->config->getProperty('exchange_properties')
        );

        $queue = $this->config->getProperty('queue');

        if (!empty($queue) || $this->config->getProperty('queue_force_declare')) {
            $this->queueInfo = $this->channel->queue_declare(
                $queue,
                $this->config->getProperty('queue_passive'),
                $this->config->getProperty('queue_durable'),
                $this->config->getProperty('queue_exclusive'),
                $this->config->getProperty('queue_auto_delete'),
                $this->config->getProperty('queue_nowait'),
                $this->config->getProperty('queue_properties')
            );

            if (!$this->config->getProperty('nobinding')) {
                $routing = $this->config->getProperty('routing');
                if (!is_array($routing)) {
                    $routing = [$routing];
                }

                foreach ($routing as $routingValue) {
                    $this->channel->queue_bind($queue ?: $this->queueInfo[0], $exchange, $routingValue);
                }
            }
        }

        foreach ($this->afterCallbacks as $callback) {
            $callback($this->channel);
        }

        // clear at shutdown
        register_shutdown_function([get_class(), 'shutdown',], $this->channel, $this->connection);
    }

    /** @inheritDoc */
    public function disconnect(): void
    {
        static::shutdown($this->channel, $this->connection);
    }

    /**
     * @param AMQPChannel $channel
     * @param AMQPStreamConnection $connection
     * @throws \Exception
     */
    public static function shutdown(AMQPChannel $channel, AMQPStreamConnection $connection)
    {
        $channel->close();
        $connection->close();
    }

    /** @inheritDoc */
    public function getChannel(): AMQPChannel
    {
        return $this->channel;
    }

    /** @inheritDoc */
    public function getExchange(): string
    {
        return $this->exchange;
    }

    /** @inheritDoc */
    public function getQueueInfo(): ?array
    {
        return $this->queueInfo;
    }
}
