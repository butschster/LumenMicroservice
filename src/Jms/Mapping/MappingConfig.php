<?php

namespace Butschster\Exchanger\Jms\Mapping;

use Illuminate\Contracts\Config\Repository;

class Config
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
