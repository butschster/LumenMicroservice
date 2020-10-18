<?php

namespace Butschster\Exchanger\Payloads\Response;

use JMS\Serializer\Annotation as JMS;

class Meta
{
    /**
     * @JMS\Type("Butschster\Exchanger\Payloads\Response\Pagination")
     */
    public ?Pagination $pagination = null;
}
