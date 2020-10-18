<?php

namespace Butschster\Exchanger\Jms\Handlers;

use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\HandlerRegistryInterface;
use Butschster\Exchanger\Contracts\Serializer;

class MappingHandler implements Serializer\Handler
{
    private array $mapping;

    public function __construct(array $mapping = [])
    {
        $this->mapping = $mapping;
    }

    /** @inheritDoc */
    public function serialize(Serializer $serializer, HandlerRegistryInterface $registry): void
    {
        foreach ($this->mapping as $mapClass) {
            $registry->registerHandler(GraphNavigatorInterface::DIRECTION_SERIALIZATION, $mapClass, 'json', function ($visitor, $obj, array $type) use ($serializer, $mapClass) {
                return $serializer->serialize($obj);
            });
        }
    }

    /** @inheritDoc */
    public function deserialize(Serializer $serializer, HandlerRegistryInterface $registry): void
    {
        foreach ($this->mapping as $mapFrom => $mapTo) {
            $registry->registerHandler(GraphNavigatorInterface::DIRECTION_DESERIALIZATION, $mapFrom, 'json', function ($visitor, $obj, array $type) use ($serializer, $mapTo) {
                if (empty($obj)) {
                    return null;
                }
                return $serializer->deserialize($obj, $mapTo);
            });
        }
    }
}
