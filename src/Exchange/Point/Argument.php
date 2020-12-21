<?php

namespace Butschster\Exchanger\Exchange\Point;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

/**
 * @internal
 */
class Argument implements Arrayable, JsonSerializable
{
    protected string $name;
    protected string $class;

    public function __construct(string $name, string $class)
    {
        $this->name = $name;
        $this->class = $class;
    }

    /**
     * Get method argument variable name
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get method argument class name
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * Check if class is in given array
     * @param string[] $classes
     * @return bool
     */
    public function is(string ...$classes): bool
    {
        return in_array($this->getClass(), $classes);
    }

    public function toArray()
    {
        return [
            'class' => $this->getClass(),
            'name' => $this->getName(),
        ];
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
