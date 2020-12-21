<?php

namespace Butschster\Exchanger\Contracts\Serializer;

use Butschster\Exchanger\Contracts\Exchange\Payload;
use JMS\Serializer\Builder\DriverFactoryInterface;

interface ObjectsMapper
{
    /**
     * Map Payload object to related object
     * @param object $payload
     * @param string|null $class
     * @return mixed
     */
    public function toObject($payload, ?string $class = null);

    /**
     * Map object to related payload
     * @param object $object
     * @param string|null $payloadClass
     * @return object
     */
    public function toPayload($object, ?string $payloadClass = null);

    /**
     * Map object to array
     * @param $object
     * @return array
     */
    public function toArray($object): array;
}
