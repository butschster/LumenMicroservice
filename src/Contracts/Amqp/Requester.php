<?php

namespace Butschster\Exchanger\Contracts\Amqp;

use PhpAmqpLib\Message\AMQPMessage;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;

interface Requester
{
    /**
     * Request an information from another exchange point
     * @param array $properties
     * @param string $route
     * @param string $message
     * @param callable $callback
     * @param int $deliveryMode
     */
    public function request(array $properties, string $route, string $message, callable $callback, int $deliveryMode = AMQPMessage::DELIVERY_MODE_PERSISTENT): void;

    /**
     * Send a deferred request
     * @param array $properties
     * @param LoopInterface $loop
     * @param string $route
     * @param string $message
     * @param int $deliveryMode
     * @return PromiseInterface
     */
    public function deferredRequest(array $properties, LoopInterface $loop, string $route, string $message, int $deliveryMode = AMQPMessage::DELIVERY_MODE_PERSISTENT): PromiseInterface;
}
