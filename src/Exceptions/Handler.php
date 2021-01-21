<?php

namespace Butschster\Exchanger\Exceptions;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Validation\ValidationException;
use NunoMaduro\Collision\Adapters\Laravel\Inspector;
use NunoMaduro\Collision\SolutionsRepositories\NullSolutionsRepository;
use NunoMaduro\Collision\Writer;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Throwable;
use Butschster\Exchanger\Contracts\Exchange\IncomingRequest;
use Symfony\Component\Console\Application as ConsoleApplication;
use Butschster\Exchanger\Payloads\Error;
use Butschster\Exchanger\Payloads\Exception;

class Handler implements ExceptionHandler
{
    protected array $dontReport = [];
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function report(Throwable $e)
    {
        if ($this->shouldntReport($e)) {
            return;
        }

        report($e);
    }

    public function shouldReport(Throwable $e)
    {
        return !$this->shouldntReport($e);
    }

    public function render($request, Throwable $e)
    {
        if ($e instanceof ValidationException) {
            $this->sendValidationErrorResponse($request, $e);
            return;
        }

        $this->sendDefaultErrorResponse($request, $e);
    }

    public function renderForConsole($output, Throwable $e)
    {
        if ($e instanceof ExceptionInterface) {
            (new ConsoleApplication)->renderThrowable($e, $output);
        } else {
            $solutionsRepository = new NullSolutionsRepository();
            $writer = new Writer($solutionsRepository);
            $handler = new \NunoMaduro\Collision\Handler($writer);

            $handler = (new \NunoMaduro\Collision\Provider(null, $handler))
                ->register()
                ->getHandler()
                ->setOutput($output);

            $handler->setInspector((new Inspector($e)));

            $handler->handle();
        }
    }

    /**
     * @param IncomingRequest $request
     * @param Throwable $e
     */
    private function sendDefaultErrorResponse(IncomingRequest $request, Throwable $e): void
    {
        $request->reply(new Exception(), [
            $this->makeErrorPayload($e),
        ]);
    }

    private function makeErrorPayload(Throwable $e): Error
    {
        $result = new Error();
        $result->code = $e->getCode();
        $result->message = $e->getMessage();


        if (env('APP_DEBUG') !== false) {
            $result->trace = array_map(function ($row) {
                $trace = new Error\Trace();
                $trace->line = $row['line'] ?? 0;
                $trace->file = $row['file'] ?? '';
                $trace->class = $row['class'] ?? '';
                $trace->function = $row['function'] ?? '';
                return $trace;
            }, $e->getTrace());
        }

        return $result;
    }

    private function shouldntReport(Throwable $e): bool
    {
        foreach ($this->dontReport as $type) {
            if ($e instanceof $type) {
                return true;
            }
        }

        return false;
    }

    private function sendValidationErrorResponse($request, ValidationException $e): void
    {
        $payload = new Error;
        $payload->message = $e->getMessage();
        $payload->code = $e->status;
        $payload->data = $e->errors();

        $request->reply(new Exception(), [
            $payload
        ]);
    }
}
