<?php

namespace Butschster\Exchanger\Exchange\Point;

use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Butschster\Exchanger\Contracts\Exchange;
use Butschster\Exchanger\Contracts\Exchange\Route;
use JsonSerializable;

/**
 * @internal
 */
class Subject implements Route, Arrayable, JsonSerializable
{
    /** Subject name */
    protected string $subject;

    protected Exchange\Point $exchange;

    /** Subject method name */
    protected string $name;

    /** Subject method summary */
    private string $description;

    /** Subject request payload */
    protected ?string $requestPayload = null;

    /** Subject response payload */
    protected ?string $responsePayload = null;

    /** Suject method arguments */
    protected Collection $arguments;

    /** Middleware */
    protected array $middleware = [];

    /** Disabled Middleware */
    protected array $disabledMiddleware = [];

    public function __construct(
        Exchange\Point $exchange,
        string $name,
        string $description,
        string $subject,
        Collection $parameters,
        array $middleware,
        array $disabledMiddleware = [],
        ?string $requestPayload = null,
        ?string $responsePayload = null
    )
    {
        $this->exchange = $exchange;
        $this->name = $name;
        $this->subject = $subject;
        $this->requestPayload = $requestPayload;
        $this->responsePayload = $responsePayload;
        $this->middleware = $middleware;
        $this->arguments = $parameters;
        $this->disabledMiddleware = $disabledMiddleware;
        $this->description = $description;
    }

    /**
     * Get class method name
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get class method summary
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Get subject name
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * Get subject request payload class name
     * @return string|null
     */
    public function getRequestPayload(): ?string
    {
        return $this->requestPayload;
    }

    /**
     * Get subject response payload class name
     * @return string|null
     */
    public function getResponsePayload(): ?string
    {
        return $this->responsePayload;
    }

    /**
     * Get class method arguments
     * @return Collection|Argument[]
     */
    public function getArguments(): Collection
    {
        return $this->arguments;
    }

    /**
     * Get all available middleware excluding disabled middleware
     * @return array
     */
    public function getMiddleware(): array
    {
        return array_diff(
            $this->middleware,
            $this->getDisabledMiddleware()
        );
    }

    /**
     * Get middleware that should be ignored for this route
     * @return array
     */
    public function getDisabledMiddleware(): array
    {
        return $this->disabledMiddleware;
    }

    /**
     * Call class method
     * @param array $dependencies
     */
    public function call(array $dependencies): void
    {
        call_user_func_array(
            [$this->exchange, $this->getName()],
            $dependencies
        );
    }

    public function toArray()
    {
        return [
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'arguments' => $this->getArguments()->toArray(),
            'middleware' => $this->getMiddleware(),
            'disabledMiddleware' => $this->getDisabledMiddleware(),
            'request_payload' => $this->getRequestPayload(),
            'response_payload' => $this->getResponsePayload(),
        ];
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
