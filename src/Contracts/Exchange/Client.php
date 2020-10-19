<?php

namespace Butschster\Exchanger\Contracts\Exchange;

use PhpAmqpLib\Message\AMQPMessage;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;

interface Client
{
    /**
     * Subscribe exchange point to subjects connected with it
     * @param Point $exchange
     * @param callable $handler
     * @return void
     */
    public function subscribe(Point $exchange, callable $handler): void;

    /**
     * Send request with response
     * @param string $subject
     * @param string $payload
     * @param int $deliveryMode
     * @return string
     */
    public function request(string $subject, string $payload, int $deliveryMode = AMQPMessage::DELIVERY_MODE_PERSISTENT): string;

    /**
     * Send deferred request
     * @param LoopInterface $loop
     * @param string $subject
     * @param string $payload
     * @param int $deliveryMode
     * @return PromiseInterface
     */
    public function deferredRequest(LoopInterface $loop, string $subject, string $payload, int $deliveryMode = AMQPMessage::DELIVERY_MODE_PERSISTENT): PromiseInterface;

    /**
     * Broadcast a message
     * @param string $subject
     * @param string $payload
     * @param int $deliveryMode
     */
    public function broadcast(string $subject, string $payload, int $deliveryMode = AMQPMessage::DELIVERY_MODE_PERSISTENT): void;

    /**
     * Set property to AMPQ connector
     * @param string $property
     * @param mixed $value
     *
     * @return void
     */
    public function setProperty(string $property, $value): void;
}
