<?php

namespace Butschster\Exchanger\Contracts\Exchange;

use Butschster\Exchanger\Payloads\Response;

interface Request
{
    /**
     * Send request
     *
     * @param string $responsePayload
     * @return Response
     */
    public function send(string $responsePayload): Response;

    /**
     * Broadcast data
     */
    public function call(): void;
}
