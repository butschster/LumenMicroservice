<?php

namespace Butschster\Exchanger\Exchange;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Butschster\Exchanger\Contracts\Amqp\Message;
use Butschster\Exchanger\Contracts\Exchange\IncomingRequest as IncomingRequestContract;
use Butschster\Exchanger\Contracts\Exchange\Payload;
use Butschster\Exchanger\Contracts\Exchange\PayloadFactory;
use Butschster\Exchanger\Exchange\Request\MessageValidator;
use Butschster\Exchanger\Payloads\Request\Headers as RequestHeaders;
use Butschster\Exchanger\Payloads\Response\Headers as ResponseHeaders;

class IncomingRequest implements IncomingRequestContract
{
    private Message $message;
    private Container $container;
    private ResponseHeaders $responseHeaders;
    private ?RequestHeaders $requestHeaders;
    private PayloadFactory $factory;

    public function __construct(
        PayloadFactory $factory,
        Container $container,
        Message $message,
        ?RequestHeaders $requestHeaders = null
    )
    {
        $this->factory = $factory;
        $this->message = $message;
        $this->container = $container;
        $this->requestHeaders = $requestHeaders;
        $this->responseHeaders = $this->buildResponseHeaders();
    }

    /** @inheritDoc */
    public function sendResponse(Payload $payload): void
    {
        $this->reply($payload, [], $this->responseHeaders);
    }

    /** @inheritDoc */
    public function sendEmptyResponse(): void
    {
        $this->sendResponse(
            $this->factory->createEmptyPayload()
        );
    }

    /** @inheritDoc */
    public function getBody(): string
    {
        return $this->message->getBody();
    }

    /** @inheritDoc */
    public function getSubject(): string
    {
        return $this->message->getSubject();
    }

    /** @inheritDoc */
    public function reply(Payload $message, array $errors = [], ?ResponseHeaders $headers = null): void
    {
        $this->message->reply($message, $errors, $headers);
    }

    /** @inheritDoc */
    public function validate(array $rules): void
    {
        $this->container->make(MessageValidator::class)
            ->validate($this->message, $rules);
    }

    /** @inheritDoc */
    public function withPagination(LengthAwarePaginator $paginator): void
    {
        $this->responseHeaders->paginate($paginator);
    }

    /** @inheritDoc */
    public function getPayload()
    {
        $this->message->getPayload();
    }

    /** @inheritDoc */
    public function getRequestHeaders(): ?RequestHeaders
    {
        return $this->requestHeaders;
    }

    private function buildResponseHeaders(): ?ResponseHeaders
    {
        return $this->factory->createResponseHeaders();
    }
}
