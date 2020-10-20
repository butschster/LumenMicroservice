<?php

namespace Butschster\Exchanger\Contracts\Amqp;

use PhpAmqpLib\Message\AMQPMessage;

interface Publisher
{
    /**
     * Broadcast a message to AMQP
     * @param array $properties
     * @param string $route
     * @param string $message
     * @param bool $persistent
     */
    public function publish(array $properties, string $route, string $message, bool $persistent = true): void;
}
