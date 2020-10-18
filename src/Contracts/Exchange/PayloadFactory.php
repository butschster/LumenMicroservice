<?php

namespace Butschster\Exchanger\Contracts\Exchange;

use Butschster\Exchanger\Payloads\Error;
use Butschster\Exchanger\Payloads\Request as RequestPayload;
use Butschster\Exchanger\Payloads\Response as ResponsePayload;
use Butschster\Exchanger\Payloads\Response\Headers;

interface PayloadFactory
{
    /**
     * Create empty payload
     * @return Payload
     */
    public function createEmptyPayload(): Payload;

    /**
     * Create request payload
     * @param Payload|null $payload
     * @return RequestPayload
     */
    public function createRequest(?Payload $payload = null): RequestPayload;

    /**
     * Create request headers payload
     * @return RequestPayload\Headers
     */
    public function createRequestHeaders(): RequestPayload\Headers;

    /**
     * Create response payload
     * @param Payload|null $payload
     * @param array|Error[] $errors
     * @param Headers|null $headers
     * @return ResponsePayload
     */
    public function createResponse(?Payload $payload = null, array $errors = [], ?Headers $headers = null): ResponsePayload;

    /**
     * Create response headers payload
     * @return Headers
     */
    public function createResponseHeaders(): ResponsePayload\Headers;
}
