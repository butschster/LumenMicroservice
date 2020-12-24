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
     * @param bool $persistent
     * @return string JSON string with response
     */
    public function request(string $subject, string $payload, bool $persistent = true): string;

    /**
     * Send deferred request
     * @param LoopInterface $loop
     * @param string $subject
     * @param string $payload
     * @param bool $persistent
     * @return PromiseInterface
     */
    public function deferredRequest(LoopInterface $loop, string $subject, string $payload, bool $persistent = true): PromiseInterface;

    /**
     * Broadcast a message
     * @param string $subject
     * @param string $payload
     * @param bool $persistent
     */
    public function broadcast(string $subject, string $payload, bool $persistent = true): void;

    /**
     * Set property to AMPQ connector
     * @param string $property
     * @param mixed $value
     *
     * @return void
     */
    public function setProperty(string $property, $value): void;
}
