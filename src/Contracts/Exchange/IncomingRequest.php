<?php

namespace Butschster\Exchanger\Contracts\Exchange;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Butschster\Exchanger\Contracts\Amqp\Message;
use Butschster\Exchanger\Payloads\Request\Headers as RequestHeaders;

interface IncomingRequest extends Message
{
    /**
     * Send response to incoming request
     * @param Payload $payload
     */
    public function sendResponse(Payload $payload): void;

    /**
     * Send empty response
     */
    public function sendEmptyResponse(): void;

    /**
     * Validate incoming data
     * @param array $rules
     */
    public function validate(array $rules): void;

    /**
     * Get request headers
     * @return RequestHeaders|null
     */
    public function getRequestHeaders(): ?RequestHeaders;

    /**
     * Generate headers for pagination
     * @param LengthAwarePaginator $paginator
     */
    public function withPagination(LengthAwarePaginator $paginator): void;
}
