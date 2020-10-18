<?php

namespace Butschster\Exchanger\Payloads;

use JMS\Serializer\Annotation as JMS;

class Request implements \Butschster\Exchanger\Contracts\Exchange\Payload
{
    /** @JMS\Type("Butschster\Exchanger\Payloads\Request\Headers") */
    public ?Request\Headers $headers = null;

    /**
     * Request body
     * @var Payload|null
     */
    public ?Payload $payload = null;
}
