<?php

namespace Butschster\Exchanger\Contracts\Exchange;

use Butschster\Exchanger\Payloads\Response;

interface Request
{
    /**
     * Send request
     *
     * @param string $responsePayload
     * @param bool $persistent
     * @return Response
     */
    public function send(string $responsePayload, bool $persistent = true): Response;

    /**
     * Broadcast data
     * @param bool $persistent
     */
    public function broadcast(bool $persistent = false): void;
}
