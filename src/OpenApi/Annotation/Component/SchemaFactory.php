<?php

namespace Butschster\Exchanger\OpenApi\Annotation\Component;

class SchemaFactory
{
    /**
     * Create schemas from array of classes
     * @param array $classes
     * @return array
     * @throws \ReflectionException
     */
    public function createFromArray(array $classes): array
    {
        $components = [];

        foreach ($classes as $class => $name) {
            $components = array_merge($components, $this->createSchemaGenerator()->generate($class, $name));
        }

        return $components;
    }

    /**
     * Create schemas from class name
     *
     * @param string|null $getRequestPayload
     * @return array
     * @throws \ReflectionException
     */
    public function createFromPayload(?string $getRequestPayload): array
    {
        if (!$getRequestPayload) {
            return [];
        }

        return $this->createSchemaGenerator()->generate($getRequestPayload);
    }

    private function createSchemaGenerator(): Schema
    {
        return new Schema();
    }
}
