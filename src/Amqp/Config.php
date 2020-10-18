<?php

namespace Butschster\Exchanger\Amqp;

use Illuminate\Contracts\Config\Repository;

/**
 * @internal
 */
class Config
{
    protected array $properties = [];

    public function __construct(Repository $config)
    {
        $this->extractProperties((array)$config->get('amqp'));
    }

    public function mergeProperties(array $properties): void
    {
        $this->properties = array_merge($this->properties, $properties);
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getProperty(string $key)
    {
        return $this->properties[$key] ?? null;
    }

    private function extractProperties(array $data)
    {
        $this->properties = $data['properties'][$data['use']];
    }
}
