<?php

namespace Butschster\Exchanger\Contracts;

use JMS\Serializer\Builder\DriverFactoryInterface as Factory;
use Butschster\Exchanger\Contracts\Exchange\Payload;

interface Serializer
{
    /**
     * Serialize object to a string
     * @param Payload $object
     * @param array $mapping
     *
     * @return string
     */
    public function serialize(Payload $object, array $mapping = []): string;

    /**
     * Deserialize string to an object
     * @param string|array $data
     * @param string $class
     * @param array $mapping
     *
     * @return Payload
     */
    public function deserialize($data, string $class, array $mapping = []): Payload;
}
