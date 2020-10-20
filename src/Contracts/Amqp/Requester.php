<?php

namespace Butschster\Exchanger\Contracts\Amqp;

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
     * @param bool $persistent
     */
    public function request(array $properties, string $route, string $message, callable $callback, bool $persistent = true): void;

    /**
     * Send a deferred request
     * @param array $properties
     * @param LoopInterface $loop
     * @param string $route
     * @param string $message
     * @param bool $persistent
     * @return PromiseInterface
     */
    public function deferredRequest(array $properties, LoopInterface $loop, string $route, string $message, bool $persistent = true): PromiseInterface;
}
