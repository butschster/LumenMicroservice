<?php

namespace Butschster\Exchanger\Exchange;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Psr\Log\LoggerInterface;
use Throwable;
use Illuminate\Contracts\Debug\ExceptionHandler;

/**
 * @internal
 */
class ConsoleLogger implements LoggerInterface
{
    private Command $command;

    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    /** @inheritDoc */
    public function emergency($message, array $context = [])
    {
        $this->log('warning', $message, $context);
    }

    /** @inheritDoc */
    public function alert($message, array $context = [])
    {
        $this->log('warning', $message, $context);
    }

    /** @inheritDoc */
    public function critical($message, array $context = [])
    {
        $this->log('error', $message, $context);
    }

    /** @inheritDoc */
    public function error($message, array $context = [])
    {
        $this->log('error', $message, $context);
    }

    /** @inheritDoc */
    public function warning($message, array $context = [])
    {
        $this->log('warning', $message, $context);
    }

    /** @inheritDoc */
    public function notice($message, array $context = [])
    {
        $this->log('info', $message, $context);
    }

    /** @inheritDoc */
    public function info($message, array $context = [])
    {
        $this->log('info', $message, $context);
    }

    /** @inheritDoc */
    public function debug($message, array $context = [])
    {
        $this->log('info', $message, $context);
    }

    /** @inheritDoc */
    public function log($level, $message, array $context = [])
    {
        if (env('APP_DEBUG') === false) {
            return;
        }

        $this->command->line("<options=bold>  " . Carbon::now()->toDateTimeString()."</>");
        $this->command->line("<{$level}>  " . $message . "</{$level}>");
        $this->command->line("");

        foreach ($context as $data) {
            $this->command->line(json_encode($data, JSON_PRETTY_PRINT));
        }
    }

    public function handleException(ExceptionHandler $handler, Throwable $e): void
    {
        $handler->renderForConsole($this->command->getOutput(), $e);
    }
}
