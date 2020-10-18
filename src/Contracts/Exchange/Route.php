<?php

namespace Butschster\Exchanger\Contracts\Exchange;

use Illuminate\Support\Collection;

interface Route
{
    /**
     * Get subject method arguments
     * @return Collection
     */
    public function getArguments(): Collection;

    /**
     * Call subject
     * @param array $dependencies
     */
    public function call(array $dependencies): void;

    /**
     * Get the middlewares attached to the route.
     * @return array
     */
    public function getMiddleware(): array;
}
