<?php

namespace Butschster\Exchanger\Exchange\Point;

use Illuminate\Support\Collection;
use Butschster\Exchanger\Contracts\Exchange;
use Butschster\Exchanger\Contracts\Exchange\Route;

/**
 * @internal
 */
class Subject implements Route
{
    /** Subject name */
    protected string $subject;

    protected Exchange\Point $exchange;

    /** Subject method name */
    protected string $name;

    /** Suject method arguments */
    protected Collection $arguments;

    /** Middleware */
    protected array $middleware = [];

    /** Disabled Middleware */
    protected array $disabledMiddleware = [];

    public function __construct(
        Exchange\Point $exchange,
        string $name,
        string $subject,
        Collection $parameters,
        array $middleware,
        array $disabledMiddleware = []
    )
    {
        $this->exchange = $exchange;
        $this->name = $name;
        $this->subject = $subject;
        $this->middleware = $middleware;
        $this->arguments = $parameters;
        $this->disabledMiddleware = $disabledMiddleware;
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
     * Get subject name
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * Get class method arguments
     * @return Collection
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
}
