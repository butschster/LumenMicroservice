<?php

namespace Butschster\Exchanger\Exchange\Request;

use Closure;
use Illuminate\Contracts\Container\Container;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;
use Throwable;
use Butschster\Exchanger\Contracts\Amqp\Message;
use Butschster\Exchanger\Contracts\Exchange\IncomingRequest;
use Butschster\Exchanger\Contracts\Exchange\Route;
use Butschster\Exchanger\Contracts\Serializer;
use Butschster\Exchanger\Exceptions\Handler;
use Butschster\Exchanger\Exceptions\ParameterCannotBeResolvedException;
use Butschster\Exchanger\Exchange\Point\Argument;
use Butschster\Exchanger\Payloads\Request\Headers as RequestHeaders;

class Dispatcher
{
    private Pipeline $pipeline;
    private Serializer $serializer;
    private Container $container;
    private Handler $exceptionsHandler;
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        Handler $exceptionsHandler,
        Container $container,
        Serializer $serializer,
        Pipeline $pipeline
    )
    {
        $this->pipeline = $pipeline;
        $this->serializer = $serializer;
        $this->container = $container;
        $this->exceptionsHandler = $exceptionsHandler;
        $this->logger = $logger;
    }

    /**
     * Route received message to exchange point subject
     * @param Message $message
     * @param Route $route
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function dispatch(Message $message, Route $route): void
    {
        $request = $this->makeIncomingRequest($message);

        try {
            $dependencies = $this->resolveDependencies(
                $request, $route->getArguments()
            );

            $this->sendThroughPipeline($route->getMiddleware(), function () use ($route, $dependencies) {
                $route->call($dependencies);
            });
        } catch (\Throwable $e) {
            $this->handleException($request, $e);
        }
    }

    /**
     * Send message through middleware and then call subject method
     * @param array $middleware
     * @param Closure $then
     *
     * @return void
     */
    private function sendThroughPipeline(array $middleware, Closure $then): void
    {
        if (count($middleware) > 0) {
            $this->pipeline
                ->send($this)
                ->through($middleware)
                ->then($then);

            return;
        }

        $then();
    }

    /**
     * Inject dependencies to subject method
     * @param IncomingRequest $request
     * @param Collection $dependencies
     *
     * @return array
     */
    private function resolveDependencies(IncomingRequest $request, Collection $dependencies): array
    {
        return $dependencies->map(function (Argument $dependency) use ($request) {
            return $this->resolveDependency($request, $dependency);
        })->toArray();
    }

    /**
     * Resolve dependencies from exchange point subject method
     * @param IncomingRequest $request
     * @param Argument $dependency
     * @return object
     */
    private function resolveDependency(IncomingRequest $request, Argument $dependency)
    {
        if ($dependency->is(Message::class, IncomingRequest::class)) {
            return $request;
        }

        if ($dependency->is(LoggerInterface::class)) {
            return $this->logger;
        }

        throw new ParameterCannotBeResolvedException(
            $dependency->getName()
        );
    }

    /**
     * Handle exceptions
     * @param IncomingRequest $request
     * @param Throwable $e
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function handleException(IncomingRequest $request, Throwable $e): void
    {
        $this->exceptionsHandler->report($e);
        $this->exceptionsHandler->render($request, $e);

        if (method_exists($this->logger, 'handleException')) {
            $this->logger->handleException($this->exceptionsHandler, $e);
        }
    }

    /**
     * Incomming request factory
     * @param Message $message
     * @return \Butschster\Exchanger\Exchange\IncomingRequest
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function makeIncomingRequest(Message $message): \Butschster\Exchanger\Exchange\IncomingRequest
    {
        $body = $message->getBody();

        /** @var RequestHeaders|null $headers */
        $headers = null;
        if (isset($body->headers)) {
            $headers = $this->serializer->deserialize(
                $body->headers,
                RequestHeaders::class
            );
        }

        return $this->container->make(\Butschster\Exchanger\Exchange\IncomingRequest::class, [
            'message' => $message,
            'headers' => $headers
        ]);
    }
}
