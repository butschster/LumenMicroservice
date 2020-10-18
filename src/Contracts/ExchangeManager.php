<?php

namespace Butschster\Exchanger\Contracts;

use Butschster\Exchanger\Contracts\Exchange\Request;

interface ExchangeManager
{
    /**
     * Get service version
     * @return string|null
     */
    public function getVersion(): ?string;

    /**
     * Get service name
     * @return string
     */
    public function getName(): string;

    /**
     * Register exchange point with all subjects
     * @param Exchange\Point $exchange
     */
    public function register(Exchange\Point $exchange): void;

    /**
     * Create request
     * @param string $subject
     * @param Exchange\Payload|null $payload
     *
     * @return Request
     */
    public function request(string $subject, ?Exchange\Payload $payload = null): Request;

    /**
     * Broadcast message
     * @param string $subject
     * @param Exchange\Payload|null $payload
     *
     * @return void
     */
    public function broadcast(string $subject, ?Exchange\Payload $payload = null): void;
}
