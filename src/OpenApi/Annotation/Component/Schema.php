<?php

namespace Butschster\Exchanger\OpenApi\Annotation\Component;

use Butschster\Exchanger\Contracts\OpenApi\WithSchemas;
use Doctrine\Common\Annotations\AnnotationReader;
use JMS\Serializer\Metadata\Driver\AnnotationDriver;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use Metadata\ClassMetadata as BaseClassMetadata;
use ReflectionClass;

class Schema implements WithSchemas
{
    private string $class;
    private ReflectionClass $refl;
    private ?string $alias;
    private array $schemas = [];

    /**
     * Generate schema data and return array of schema components
     * @param string $class
     * @param string|null $alias
     * @return array
     * @throws \ReflectionException
     */
    public function generate(string $class, ?string $alias = null): array
    {
        $this->class = $class;
        $this->refl = new ReflectionClass($class);
        $this->alias = $alias ?: class_basename($class);

        return $this->getSchemas();
    }

    public function getSchemas(): array
    {
        $properties = $this->getProperties();

        $this->schemas[$this->alias] = [
            'type' => 'object',
            'properties' => $properties
        ];

        return $this->schemas;
    }

    protected function getProperties(): array
    {
        $properties = [];

        try {
            $metadata = $this->getClassPropertyMetadata();
        } catch (\Exception $e) {
            return [];
        }

        foreach ($metadata->propertyMetadata as $propertyMetadata) {
            $property = new Property(new SchemaFactory());
            $properties = array_merge($properties, $property->generate($propertyMetadata));
            $this->schemas = array_merge($this->schemas, $property->getSchemas());
        }

        return $properties;
    }

    private function getClassPropertyMetadata(): BaseClassMetadata
    {
        $classData = new AnnotationDriver(new AnnotationReader(), new IdenticalPropertyNamingStrategy());

        return $classData->loadMetadataForClass($this->refl);
    }
}
