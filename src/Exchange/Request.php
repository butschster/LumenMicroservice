<?php

namespace Butschster\Exchanger\Exchange;

use Butschster\Exchanger\Contracts\Exchange\Client;
use Butschster\Exchanger\Contracts\Exchange\Payload;
use Butschster\Exchanger\Contracts\Exchange\PayloadFactory;
use Butschster\Exchanger\Contracts\Exchange\Request as RequestContract;
use Butschster\Exchanger\Contracts\Serializer;
use Butschster\Exchanger\Payloads\Request as RequestPayload;
use Butschster\Exchanger\Payloads\Response as ResponsePayload;

class Request implements RequestContract
{
    private Serializer $serializer;
    private Client $client;
    private string $subject;
    private RequestPayload $payload;

    public function __construct(
        PayloadFactory $factory,
        Serializer $serializer,
        Client $client,
        string $subject,
        Payload $payload
    )
    {
        $this->serializer = $serializer;
        $this->client = $client;
        $this->subject = $subject;
        $this->payload = $factory->createRequest($payload);
    }

    /** @inheritDoc */
    public function send(string $responsePayload): ResponsePayload
    {
        return $this->makeResponse(
            $this->sendRequest(),
            $responsePayload
        );
    }

    /** @inheritDoc */
    public function call(): void
    {
        $this->client->call(
            $this->subject,
            $this->serializer->serialize($this->payload)
        );
    }

    private function makeResponse(string $response, string $responseClass): ResponsePayload
    {
        $response = new Response($this->serializer, $response);

        return $response->mapClass($responseClass);
    }

    /**
     * Send request and get response
     * @return string
     */
    private function sendRequest(): string
    {
        return $this->client->request(
            $this->subject,
            $this->serializer->serialize($this->payload)
        );
    }
}
