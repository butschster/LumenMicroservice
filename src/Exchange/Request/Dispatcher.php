<?php

namespace Butschster\Exchanger\Exchange\Request;

use Butschster\Exchanger\Contracts\Exchange\Request\TokenDecoder;
use Butschster\Exchanger\Events\Route\Dispatched;
use Butschster\Exchanger\Events\Route\ExceptionThrown;
use Closure;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher as EventsDispatcher;
use Illuminate\Pipeline\Pipeline;
use Throwable;
use Butschster\Exchanger\Contracts\Amqp\Message;
use Butschster\Exchanger\Contracts\Exchange\IncomingRequest;
use Butschster\Exchanger\Contracts\Exchange\Route;
use Butschster\Exchanger\Contracts\Serializer;
use Butschster\Exchanger\Exceptions\Handler;
use Butschster\Exchanger\Payloads\Request\Headers as RequestHeaders;

/**
 * @internal
 */
class Dispatcher
{
    private Pipeline $pipeline;
    private Serializer $serializer;
    private Container $container;
    private Handler $exceptionsHandler;
    private DependencyResolver $dependencyResolver;
    private EventsDispatcher $events;

    public function __construct(
        Handler $exceptionsHandler,
        EventsDispatcher $events,
        Container $container,
        Serializer $serializer,
        Pipeline $pipeline,
        DependencyResolver $dependencyResolver
    )
    {
        $this->pipeline = $pipeline;
        $this->serializer = $serializer;
        $this->container = $container;
        $this->exceptionsHandler = $exceptionsHandler;
        $this->dependencyResolver = $dependencyResolver;
        $this->events = $events;
    }

    /**
     * Route received message to exchange point subject
     * @param Message $message
     * @param Route $route
     * @throws BindingResolutionException
     */
    public function dispatch(Message $message, Route $route): void
    {
        $request = $this->makeIncomingRequest($message);

        try {
            $dependencies = $this->dependencyResolver->resolve(
                $request, $route->getArguments()
            );

            $this->sendThroughPipeline($route->getMiddleware(), function () use ($route, $request, $dependencies) {
                $route->call($dependencies);

                $this->events->dispatch(
                    new Dispatched($route, $request)
                );
            });
        } catch (Throwable $e) {
            $this->handleException($request, $e);

            $this->events->dispatch(
                new ExceptionThrown($route, $request, $e)
            );
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
     * Handle exceptions
     * @param IncomingRequest $request
     * @param Throwable $e
     * @return void
     * @throws BindingResolutionException
     */
    private function handleException(IncomingRequest $request, Throwable $e): void
    {
        $this->exceptionsHandler->report($e);
        $this->exceptionsHandler->render($request, $e);
    }

    /**
     * Incomming request factory
     * @param Message $message
     * @return IncomingRequest
     * @throws BindingResolutionException
     */
    private function makeIncomingRequest(Message $message): IncomingRequest
    {
        return $this->container->make(IncomingRequest::class, [
            'message' => $message,
            'headers' => $this->parseHeaders(
                $message->getPayload('headers', RequestHeaders::class)
            )
        ]);
    }

    /**
     * @param RequestHeaders|null $headers
     * @return RequestHeaders|null
     * @throws BindingResolutionException
     */
    private function parseHeaders(?RequestHeaders $headers = null): ?RequestHeaders
    {
        if (!empty($headers->token)) {
            $headers->tokenInfo = $this->container->make(TokenDecoder::class)
                ->decode($headers->token);
        }

        return $headers;
    }
}
