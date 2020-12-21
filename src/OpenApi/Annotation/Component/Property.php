<?php

namespace Butschster\Exchanger\OpenApi\Annotation\Component;

use Butschster\Exchanger\Contracts\OpenApi\WithSchemas;
use JMS\Serializer\Metadata\PropertyMetadata;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionProperty;

class Property implements WithSchemas
{
    private array $schemas = [];
    private SchemaFactory $factory;

    public function __construct(SchemaFactory $factory)
    {
        $this->factory = $factory;
    }

    public function getSchemas(): array
    {
        return $this->schemas;
    }

    /**
     * @param PropertyMetadata $propertyMetadata
     * @return \string[][]
     * @throws \ReflectionException
     */
    public function generate(PropertyMetadata $propertyMetadata): array
    {
        $propertyClass = new ReflectionProperty($propertyMetadata->class, $propertyMetadata->name);
        $data = [];

        $summary = '';
        if ($propertyClass->getDocComment()) {
            $docBlock = DocBlockFactory::createInstance()->create($propertyClass->getDocComment());
            $summary = $docBlock->getSummary();
        }

        $data = [
            'description' => $summary
        ];

        $type = $propertyMetadata->type['name'] ?? 'string';

        switch ($type) {
            case 'ArrayCollection':
            case 'ArrayIterator':
            case 'Generator':
            case 'Iterator':
            case 'iterable':
            case 'array':
                $data = array_merge($data, $this->prepareArrayType($propertyMetadata));
                $data['type'] = 'array';
                break;
            case 'boolean':
            case 'bool':
                $data['type'] = 'boolean';
                break;
            case 'integer':
            case 'int':
                $data['type'] = 'integer';
                break;
            case 'double':
            case 'float':
                $data['type'] = 'number';
                break;
            case 'string':
                $data['type'] = 'string';
                break;
            case 'DateTime':
            case 'DateTimeImmutable':
            case 'DateTimeInterface':
            case 'Carbon\Carbon':
                $data['type'] = 'string';
                $data['format'] = 'date-time';
                break;
            default:
                if (class_exists($type)) {
                    $data['$ref'] = $this->parseTypeSchema($type);
                } else {
                    $data['type'] = $type;
                }
        }

        return [
            $propertyMetadata->name => $data
        ];
    }

    private function prepareArrayType(PropertyMetadata $propertyMetadata): array
    {
        $data = [];

        if (isset($propertyMetadata->type['params'][0]['name'])) {
            $itemsTypes = $propertyMetadata->type['params'][0]['name'];
        } else {
            $itemsTypes = 'string';
        }

        $type = $this->parseTypeSchema($itemsTypes);

        if ($type === $itemsTypes) {
            $data['items'] = [
                'type' => $type
            ];
        } else {
            $data['items'] = [
                '$ref' => $type
            ];
        }

        return $data;
    }

    private function parseTypeSchema(string $class): string
    {
        if (class_exists($class)) {
            $this->schemas = array_merge($this->schemas, $this->factory->createFromPayload($class));

            return '#/components/schemas/' . class_basename($class);
        }


        return $class;
    }
}
