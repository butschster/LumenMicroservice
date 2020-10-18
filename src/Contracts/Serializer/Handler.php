<?php

namespace Butschster\Exchanger\Contracts\Serializer;

use JMS\Serializer\Handler\HandlerRegistryInterface;
use Butschster\Exchanger\Contracts\Serializer;

interface Handler
{
    /**
     * Register serialize handler
     * @param Serializer $serializer
     * @param HandlerRegistryInterface $registry
     */
    public function serialize(Serializer $serializer, HandlerRegistryInterface $registry): void;

    /**
     * Register deserialize handler
     * @param Serializer $serializer
     * @param HandlerRegistryInterface $registry
     */
    public function deserialize(Serializer $serializer, HandlerRegistryInterface $registry): void;
}
