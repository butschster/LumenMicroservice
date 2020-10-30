<?php

namespace Butschster\Exchanger\Payloads;

use Butschster\Exchanger\Contracts\Exchange\Payload as PayloadContract;
use JMS\Serializer\Annotation as JMS;

class Request implements PayloadContract
{
    /** @JMS\Type("Butschster\Exchanger\Payloads\Request\Headers") */
    public ?Request\Headers $headers = null;

    /**
     * Request body
     */
    public ?PayloadContract $payload = null;
}
