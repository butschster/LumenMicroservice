<?php

namespace Butschster\Exchanger\Jms;

use Butschster\Exchanger\Exceptions\ObjectMapperNotFound;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\Arr;

class Config
{
    private Repository $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function getHandlers(): array
    {
        return (array)$this->repository->get('serializer.handlers', []);
    }

    public function getMappingData(): array
    {
        return (array)$this->repository->get('serializer.mapping', []);
    }

    /**
     * Get mapping data for given class by name
     *
     * @param string $class
     * @return array|null
     */
    public function getClassMap(string $class): ?array
    {
        return $this->getMappingData()[$class] ?? null;
    }

    /**
     * Find object class name by payload class name
     * @param string $class
     * @return string
     */
    public function findRelatedClassForPayload(string $class): string
    {
        foreach ($this->getMappingData() as $objectClass => $config) {
            if (isset($config['to']) && $config['to'] == $class) {
                return $objectClass;
            }
        }

        throw new ObjectMapperNotFound(
            sprintf('Mapper for class %s is not found.', $class)
        );
    }

    /**
     * Find payload class name by object class name
     * @param string $class
     * @return string
     */
    public function findPayloadForRelatedObject(string $class): string
    {
        $mappingData = $this->getMappingData();

        $payloadClass = Arr::get($mappingData, $class . '.to');
        if (!$payloadClass) {
            foreach ($mappingData as $objectClass => $data) {
                if (!isset($data['aliases'])) {
                    continue;
                }

                $aliases = (array)$data['aliases'];

                if (in_array($class, $aliases)) {
                    $payloadClass = $data['to'] ?? null;
                    break;
                }
            }
        }

        if (is_null($payloadClass)) {
            throw new ObjectMapperNotFound(
                sprintf('Mapper for class %s is not found.', $class)
            );
        }

        return $payloadClass;
    }
}
