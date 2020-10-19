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
     * @param int $deliveryMode
     */
    public function publish(array $properties, string $route, string $message, int $deliveryMode = AMQPMessage::DELIVERY_MODE_PERSISTENT): void;
}
