<?php

namespace Butschster\Exchanger\Contracts\Amqp;

use Closure;
use PhpAmqpLib\Message\AMQPMessage;

interface Consumer
{
    /**
     * Subscribe to AMQP subjects
     * @param array $properties
     * @param string $queue
     * @param Closure $closure
     * @return bool
     */
    public function consume(array $properties, string $queue, Closure $closure): bool;

    /**
     * Send information to AMQP about about handled message
     * @param AMQPMessage $message
     */
    public function acknowledge(AMQPMessage $message): void;

    /**
     * Reply to a received message
     * @param AMQPMessage $message
     * @param string|null $replyTo
     */
    public function reply(AMQPMessage $message, ?string $replyTo = null): void;

    /**
     * Reject a message
     * @param AMQPMessage $message
     * @param bool $requeue
     */
    public function reject(AMQPMessage $message, bool $requeue = false): void;
}
