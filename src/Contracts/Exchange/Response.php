<?php

namespace Butschster\Exchanger\Contracts\Exchange;

use Butschster\Exchanger\Payloads\Response as ResponsePayload;

interface Response
{
    /**
     * Get response body
     * @return string
     */
    public function getResponse(): string;

    /**
     * Map response to a class
     * @param string $class
     * @return ResponsePayload
     */
    public function mapClass(string $class): ResponsePayload;

    /**
     * Map response to a class map
     * @param array $mapping
     * @return ResponsePayload
     */
    public function map(array $mapping = []): ResponsePayload;
}
