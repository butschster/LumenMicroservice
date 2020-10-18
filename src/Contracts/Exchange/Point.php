<?php

namespace Butschster\Exchanger\Contracts\Exchange;

interface Point
{
    /**
     * Get access point key. It will be used as a prefix for subjects
     * @return string
     */
    public function getName(): string;
}
