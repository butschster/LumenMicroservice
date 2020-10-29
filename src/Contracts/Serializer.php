<?php

namespace Butschster\Exchanger\Contracts;

use JMS\Serializer\Builder\DriverFactoryInterface;
use JMS\Serializer\Builder\DriverFactoryInterface as Factory;

interface Serializer
{
    /**
     * Serialize object to a string
     * @param object $object
     * @param array $mapping
     * @param Factory|null $mappingDriver
     * @return string
     */
    public function serialize($object, array $mapping = [], ?DriverFactoryInterface $mappingDriver = null): string;

    /**
     * Deserialize string to an object
     * @param string|array $data
     * @param string $class
     * @param array $mapping
     * @param DriverFactoryInterface|null $mappingDriver
     * @return object
     */
    public function deserialize($data, string $class, array $mapping = [], ?DriverFactoryInterface $mappingDriver = null);
}
