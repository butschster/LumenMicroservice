<?php

namespace Butschster\Exchanger\Jms;

use Illuminate\Contracts\Config\Repository;

class MappingConfig
{
    private Repository $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllClasses(): array
    {
        return (array) $this->repository->get('mapping', []);
    }

    public function getClassMap(string $className): ?array
    {
        return $this->repository->get('mapping.'.$className);
    }
}
