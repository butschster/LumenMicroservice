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

    /** Global Middleware */
    protected static array $defaultMiddleware = [
        //
    ];

    public function __construct(
        Exchange\Point $exchange,
        string $name,
        string $subject,
        array $middleware,
        Collection $parameters,
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
     * Get subject name
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    public static function getDefaultMiddleware(): array
    {
        return self::$defaultMiddleware;
    }

    public function getMiddleware(): array
    {
        return array_diff(
            array_merge(self::getDefaultMiddleware(), $this->middleware),
            $this->getDisabledMiddleware()
        );
    }

    public function getDisabledMiddleware(): array
    {
        return $this->disabledMiddleware;
    }

    public function getExchange(): Exchange\Point
    {
        return $this->exchange;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getArguments(): Collection
    {
        return $this->arguments;
    }

    public function call(array $dependencies): void
    {
        call_user_func_array(
            [$this->getExchange(), $this->getName(),],
            $dependencies
        );
    }
}
