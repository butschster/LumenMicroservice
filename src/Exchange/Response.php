<?php

namespace Butschster\Exchanger\Exchange;

use Butschster\Exchanger\Contracts\Exchange\Response as ResponseContract;
use Butschster\Exchanger\Contracts\Serializer;
use Butschster\Exchanger\Payloads\Payload;
use Butschster\Exchanger\Payloads\Response as ResponsePayload;

class Response implements ResponseContract
{
    private string $response;
    private Serializer $serializer;

    public function __construct(Serializer $serializer, string $response)
    {
        $this->response = $response;
        $this->serializer = $serializer;
    }

    /** @inheritDoc */
    public function getResponse(): string
    {
        return $this->response;
    }

    /** @inheritDoc */
    public function mapClass(string $class): ResponsePayload
    {
        return $this->map([
            Payload::class => $class,
        ]);
    }

    /** @inheritDoc */
    public function map(array $mapping = []): ResponsePayload
    {
        return $this->serializer->deserialize(
            $this->response,
            ResponsePayload::class,
            $mapping
        );
    }
}
