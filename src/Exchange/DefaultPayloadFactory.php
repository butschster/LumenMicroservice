<?php

namespace Butschster\Exchanger\Exchange;

use Carbon\Carbon;
use Butschster\Exchanger\Contracts\Exchange\Payload;
use Butschster\Exchanger\Contracts\Exchange\PayloadFactory;
use Butschster\Exchanger\Contracts\ExchangeManager;
use Butschster\Exchanger\Payloads\Request as RequestPayload;
use Butschster\Exchanger\Payloads\Response as ResponsePayload;
use Butschster\Exchanger\Payloads\Response\Headers as ResponseHeaders;
use Butschster\Exchanger\Payloads\Response\Meta;

class DefaultPayloadFactory implements PayloadFactory
{
    private ExchangeManager $manager;

    public function __construct(ExchangeManager $manager)
    {
        $this->manager = $manager;
    }

    /** @inheritDoc */
    public function createRequest(?Payload $body = null): RequestPayload
    {
        $payload = new RequestPayload();
        $payload->payload = $body ?: new \Butschster\Exchanger\Payloads\Payload();
        $payload->headers = $this->createRequestHeaders();

        return $payload;
    }

    /** @inheritDoc */
    public function createRequestHeaders(): RequestPayload\Headers
    {
        $headers = new RequestPayload\Headers();
        $headers->version = $this->manager->getVersion();
        $headers->requester = $this->manager->getName();
        $headers->timestamp = Carbon::now();
        $headers->meta = new RequestPayload\Meta();

        return $headers;
    }

    /** @inheritDoc */
    public function createResponse(?Payload $body = null, array $errors = [], ?ResponsePayload\Headers $headers = null): ResponsePayload
    {
        $response = new ResponsePayload();
        $response->success = count($errors) == 0;
        $response->headers = $headers ?: $this->createResponseHeaders();
        $response->payload = $body;
        $response->errors = $errors;

        return $response;
    }

    /** @inheritDoc */
    public function createResponseHeaders(): ResponsePayload\Headers
    {
        $headers = new ResponseHeaders();
        $headers->meta = new Meta();

        return $headers;
    }

    /** @inheritDoc */
    public function createEmptyPayload(): Payload
    {
        return new \Butschster\Exchanger\Payloads\Payload();
    }
}
