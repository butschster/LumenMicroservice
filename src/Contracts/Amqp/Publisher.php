<?php

namespace Butschster\Exchanger\Contracts\Amqp;

interface Publisher
{
    /**
     * Broadcast a message to AMQP
     * @param array $properties
     * @param string $route
     * @param string $message
     */
    public function publish(array $properties, string $route, string $message): void;
}
