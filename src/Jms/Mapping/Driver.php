<?php

namespace Butschster\Exchanger\Jms\Mapping;

use Butschster\Exchanger\Jms\Config;
use Illuminate\Support\Str;
use JMS\Serializer\Type\ParserInterface;
use JMS\Serializer\Metadata\ClassMetadata;
use Metadata\Driver\AdvancedDriverInterface;
use Metadata\Driver\DriverInterface;
use ReflectionClass;

class Driver implements AdvancedDriverInterface
{
    private Config $config;
    private ?DriverInterface $parent;
    private ParserInterface $parser;

    public function __construct(Config $config, ParserInterface $parser, ?DriverInterface $parent = null)
    {
        $this->config = $config;
        $this->parent = $parent;
        $this->parser = $parser;
    }

    /** @inheritDoc */
    public function getAllClassNames(): array
    {
        return array_keys($this->config->getMappingData());
    }

    public function loadMetadataForClass(ReflectionClass $class): ?ClassMetadata
    {
        $className = $class->getName();

        if ($this->parent) {
            $classMetadata = $this->parent->loadMetadataForClass($class);
        } else {
            $classMetadata = new ClassMetadata($className);
        }

        $attributes = $this->getAttributesFor($className);

        foreach ($attributes as $property => $metaData) {
            if (!$class->hasProperty($property)) {
                continue;
            }

            $propertyMetadata = new PropertyMetadata($className, $property);
            $propertyMetadata->serializedName = Str::snake($property);

            if (!empty($propertyMetadata) && is_array($metaData)) {
                foreach ($metaData as $key => $value) {
                    if ($key == 'type') {
                        $propertyMetadata->setType(
                            $this->parser->parse($value)
                        );
                    } else {
                        $propertyMetadata->{$key} = $value;
                    }
                }

                $classMetadata->addPropertyMetadata($propertyMetadata);
            }
        }

        return $classMetadata;
    }

    /**
     * Gets class attributes from config
     * @param string $class
     * @return array
     */
    private function getAttributesFor(string $class): array
    {
        $classMap = $this->config->getClassMap($class);

        if (!$classMap) {
            return [];
        }

        $attributes = $classMap['attributes'] ?? [];

        if (isset($classMap['extends'])) {
            $parentAttributes = $this->getAttributesFor($classMap['extends']);

            $attributes = array_merge($attributes, $parentAttributes);
        }

        return $attributes;
    }
}
