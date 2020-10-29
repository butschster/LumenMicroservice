<?php

namespace Butschster\Exchanger\Jms;

use Butschster\Exchanger\Contracts\Serializer;
use JMS\Serializer\Builder\DriverFactoryInterface;

class ObjectsMapper implements Serializer\ObjectsMapper
{
    private DriverFactoryInterface $driverFactory;
    private Serializer $serializer;
    private Config $config;

    public function __construct(
        DriverFactoryInterface $driverFactory,
        Serializer $serializer,
        Config $config
    )
    {
        $this->driverFactory = $driverFactory;
        $this->serializer = $serializer;
        $this->config = $config;
    }

    /** @inheritDoc */
    public function toObject($payload, ?string $class = null)
    {
        if (!$class) {
            $class = $this->config->findRelatedClassForPayload(get_class($payload));
        }

        return $this->serializer->deserialize(
            $this->serializer->serialize($payload),
            $class,
            [],
            $this->driverFactory
        );
    }

    /** @inheritDoc */
    public function toPayload($object, ?string $payloadClass = null)
    {
        if (!$payloadClass) {
            $payloadClass = $this->config->findPayloadForRelatedObject(get_class($object));
        }

        return $this->serializer->deserialize(
            $this->serializer->serialize($object, [], $this->driverFactory),
            $payloadClass
        );
    }

    /** @inheritDoc */
    public function toArray($object): array
    {
        return json_decode(
            $this->serializer->serialize($object),
            true
        );
    }
}
