<?php

namespace Butschster\Exchanger\Contracts\Amqp;

use Butschster\Exchanger\Contracts\Exchange\Payload;
use Butschster\Exchanger\Payloads\Response;

interface Message
{
    /**
     * Get payload object
     * @return object
     */
    public function getPayload();

    /**
     * Get payload as serialized string (json)
     * @return string
     */
    public function getBody(): string;

    /**
     * Get subject key
     * @return string
     */
    public function getSubject(): string;

    /**
     * Reply to received message
     * @param Payload $payload
     * @param array $errors
     * @param Response\Headers|null $headers
     */
    public function reply(Payload $payload, array $errors = [], ?Response\Headers $headers = null): void;

    /**
     * Confirm message receiving
     */
    public function acknowledge(): void;
}
