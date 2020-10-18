<?php

namespace Butschster\Exchanger\Jms;

use JMS\Serializer\Context;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Handler\HandlerRegistryInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;
use Butschster\Exchanger\Contracts\Exchange\Payload;
use Butschster\Exchanger\Contracts\Serializer as SerializerContract;
use Butschster\Exchanger\Exchange\Config;
use Butschster\Exchanger\Jms\Handlers\CarbonHandler;
use Butschster\Exchanger\Jms\Handlers\MappingHandler;

class Serializer implements SerializerContract
{
    private SerializerBuilder $builder;
    private Config $config;

    public function __construct(Config $config, SerializerBuilder $builder)
    {
        $this->builder = $builder;
        $this->config = $config;
    }

    /** @inheritDoc */
    public function serialize(Payload $object, array $mapping = []): string
    {
        $this->configureSerializer($mapping);

        $result = $this->builder->build()
            ->serialize($object, 'json', $this->createContext(SerializationContext::class));

        if (method_exists($result, 'afterSerialized')) {
            $result->afterSerialized($this);
        }

        return $result;
    }

    /** @inheritDoc */
    public function deserialize($data, string $class, array $mapping = []): Payload
    {
        if (!is_string($data)) {
            $data = json_encode($data);
        }

        $this->configureDeserializer($mapping);

        $result = $this->builder->build()
            ->deserialize($data, $class, 'json', $this->createContext(DeserializationContext::class));

        if (method_exists($result, 'afterDeserialized')) {
            $result->afterDeserialized($this);
        }

        return $result;
    }

    /**
     * Create context and inject service version
     * @param string $context
     * @return Context
     */
    private function createContext(string $context): Context
    {
        $context = new $context();

        $context->setVersion($this->config->version());

        return $context;
    }

    /**
     * Register serializer handlers
     * @param array $mapping
     */
    private function configureSerializer(array $mapping): void
    {
        $this->builder->configureHandlers(function (HandlerRegistryInterface $registry) use ($mapping) {
            (new MappingHandler($mapping))->serialize($this, $registry);
            (new CarbonHandler())->serialize($this, $registry);
        });
    }

    /**
     * Register deserializer handlers
     * @param array $mapping
     */
    private function configureDeserializer(array $mapping)
    {
        $this->builder->configureHandlers(function (HandlerRegistryInterface $registry) use ($mapping) {
            (new MappingHandler($mapping))->deserialize($this, $registry);
            (new CarbonHandler())->deserialize($this, $registry);
        });
    }
}
