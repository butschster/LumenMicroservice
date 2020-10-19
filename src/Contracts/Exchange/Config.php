<?php

namespace Butschster\Exchanger\Contracts\Exchange;

interface Config
{
    /**
     * Get service name
     * @return string
     */
    public function name(): string;

    /**
     * Get service version
     * @return string
     */
    public function version(): string;
}
