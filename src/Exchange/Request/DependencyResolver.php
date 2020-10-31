<?php

namespace Butschster\Exchanger\Exchange\Request;

use Butschster\Exchanger\Contracts\Amqp\Message;
use Butschster\Exchanger\Contracts\Exchange\IncomingRequest;
use Butschster\Exchanger\Exceptions\ParameterCannotBeResolvedException;
use Butschster\Exchanger\Exchange\Point\Argument;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;

/**
 * @internal
 */
class DependencyResolver
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function resolve(IncomingRequest $request, Collection $dependencies): array
    {
        return $dependencies->map(function (Argument $dependency) use ($request) {
            return $this->resolveDependency($request, $dependency);
        })->toArray();
    }

    /**
     * Resolve dependencies from exchange point subject method
     * @param IncomingRequest $request
     * @param Argument $dependency
     * @return mixed
     * @throws BindingResolutionException
     */
    private function resolveDependency(IncomingRequest $request, Argument $dependency)
    {
        if ($dependency->is(Message::class, IncomingRequest::class)) {
            return $request;
        }

        if ($dependency->is(LoggerInterface::class)) {
            return $this->container->make(LoggerInterface::class);
        }

        $argument = $dependency->getName();

        if ($payload = $request->getPayload($argument, $dependency->getClass())) {
            return $payload;
        }

        throw new ParameterCannotBeResolvedException(
            $dependency
        );
    }
}
