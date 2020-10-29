<?php

namespace Butschster\Exchanger\Jms;

use JMS\Serializer\Builder\DriverFactoryInterface;
use JMS\Serializer\Context;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Handler\HandlerRegistryInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;
use Butschster\Exchanger\Contracts\Serializer as SerializerContract;
use Butschster\Exchanger\Jms\Handlers\MappingHandler;

class Serializer implements SerializerContract
{
    private SerializerBuilder $builder;
    private Config $config;
    private ?string $version;

    public function __construct(SerializerBuilder $builder, Config $config, ?string $version = null)
    {
        $this->builder = $builder;
        $this->config = $config;
        $this->version = $version;
    }

    /** @inheritDoc */
    public function serialize($object, array $mapping = [], ?DriverFactoryInterface $mappingDriver = null): string
    {
        $builder = clone $this->builder;

        $this->registerSerializeHandlers($builder, $mapping);

        if ($mappingDriver) {
            $builder->setMetadataDriverFactory($mappingDriver);
        }

        $result = $builder->build()
            ->serialize($object, 'json', $this->createContext(SerializationContext::class));

        if (method_exists($result, 'afterSerialized')) {
            $result->afterSerialized($this);
        }

        return $result;
    }

    /** @inheritDoc */
    public function deserialize($data, string $class, array $mapping = [], ?DriverFactoryInterface $mappingDriver = null)
    {
        $builder = clone $this->builder;

        if (!is_string($data)) {
            $data = json_encode($data);
        }

        $this->registerDeserializeHandlers($builder, $mapping);

        if ($mappingDriver) {
            $builder->setMetadataDriverFactory($mappingDriver);
        }

        $result = $builder->build()
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
        if ($this->version) {
            $context->setVersion($this->version);
        }

        return $context;
    }

    /**
     * Register serializer handlers
     * @param SerializerBuilder $builder
     * @param array $mapping
     */
    private function registerSerializeHandlers(SerializerBuilder $builder, array $mapping): void
    {
        $builder->configureHandlers(function (HandlerRegistryInterface $registry) use ($mapping) {
            (new MappingHandler($mapping))->serialize($this, $registry);
            foreach ($this->getHandlers() as $handler) {
                $handler->serialize($this, $registry);
            }
        });
    }

    /**
     * Register deserializer handlers
     * @param SerializerBuilder $builder
     * @param array $mapping
     */
    private function registerDeserializeHandlers(SerializerBuilder $builder, array $mapping)
    {
        $builder->configureHandlers(function (HandlerRegistryInterface $registry) use ($mapping) {
            (new MappingHandler($mapping))->deserialize($this, $registry);

            foreach ($this->getHandlers() as $handler) {
                $handler->deserialize($this, $registry);
            }
        });
    }

    /**
     * @return array|SerializerContract\Handler[]
     */
    private function getHandlers(): array
    {
        return array_map(function (string $handler) {
            return new $handler();
        }, $this->config->getHandlers());
    }
}
