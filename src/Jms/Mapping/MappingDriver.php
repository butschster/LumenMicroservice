<?php

namespace Butschster\Exchanger\Jms\Mapping;

use Metadata\ClassMetadata;
use Metadata\Driver\AdvancedDriverInterface;
use ReflectionClass;

class Driver implements AdvancedDriverInterface
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /** @inheritDoc */
    public function getAllClassNames(): array
    {
        return array_keys($this->config->getAllClasses());
    }

    public function loadMetadataForClass(ReflectionClass $class): ?ClassMetadata
    {
        $className = $class->name;
        $classMetadata = new ClassMetadata($className);
        $mappingConfig = $this->config->getClassMap($className);
        if (!$mappingConfig) {
            return null;
        }

        foreach ($mappingConfig['attributes'] as $attribute => $attributeConfig) {
            if ($class->hasProperty($attribute)) {
                $propertyMetadata = new PropertyMetadata($className, $attribute);
            }

            if (!empty($propertyMetadata)) {
                foreach ($attributeConfig as $config => $value) {
                    if ($config == 'type') {
                        $propertyMetadata->setType($value);
                    } else {
                        $propertyMetadata->{$config} = $value;
                    }
                }

                $classMetadata->addPropertyMetadata($propertyMetadata);
            }
        }

        return $classMetadata;
    }
}
