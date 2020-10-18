<?php

namespace Butschster\Exchanger\Contracts\Amqp;

use PhpAmqpLib\Channel\AMQPChannel;

interface Connector
{
    /**
     * Get channel
     * @return AMQPChannel
     */
    public function getChannel(): AMQPChannel;

    /**
     * Get exchange point key
     * @return string
     */
    public function getExchange(): string;

    /**
     * Get informatino about queue
     * @return array|null
     */
    public function getQueueInfo(): ?array;

    /**
     * Connect to a channel
     * @param array $properties
     */
    public function connect(array $properties = []): void;

    /**
     * Disconnect from the channel
     */
    public function disconnect(): void;

    /**
     * Register handlers that will call after connected
     * @param callable $handler
     */
    public function afterConnect(callable $handler): void;
}
