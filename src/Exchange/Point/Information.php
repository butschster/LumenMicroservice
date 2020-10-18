<?php

namespace Butschster\Exchanger\Exchange\Point;

use Illuminate\Support\Collection;
use Butschster\Exchanger\Contracts\Exchange\Route;
use Butschster\Exchanger\Exceptions\MethodNotFoundException;

/**
 * @internal
 */
class Information
{
    protected Collection $subjects;

    public function __construct(Collection $subjects)
    {
        $this->subjects = $subjects;
    }

    /**
     * Get list of routes (exchange point subjects)
     * @return Collection
     */
    public function getRoutes(): Collection
    {
        return $this->subjects;
    }

    /**
     * Get exchange point subjects
     * @return array
     */
    public function getRouteSubjects(): array
    {
        return $this->getRoutes()->map(function (Subject $method) {
            return $method->getSubject();
        })->toArray();
    }

    /**
     * Get route (subject) by name
     * @param string $subject
     * @return Route|null
     */
    public function getRoute(string $subject): Route
    {
        $route = $this->getRoutes()->first(function (Subject $method) use ($subject) {
            return $method->getSubject() == $subject;
        });

        if (!$route) {
            throw new MethodNotFoundException($subject);
        }

        return $route;
    }
}
