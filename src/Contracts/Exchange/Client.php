<?php

namespace Butschster\Exchanger\Contracts\Exchange;

use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use Butschster\Exchanger\Exchange\Point\Information;

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
     * @return string
     */
    public function request(string $subject, string $payload): string;

    /**
     * Send deferred request
     * @param LoopInterface $loop
     * @param string $subject
     * @param string $payload
     * @return PromiseInterface
     */
    public function deferredRequest(LoopInterface $loop, string $subject, string $payload): PromiseInterface;

    /**
     * Broadcast a message
     * @param string $subject
     * @param string $payload
     */
    public function call(string $subject, string $payload): void;

    /**
     * Set property to AMPQ connector
     * @param string $property
     * @param mixed $value
     *
     * @return void
     */
    public function setProperty(string $property, $value): void;
}
