<?php

namespace Butschster\Exchanger\Payloads\Request;

use JMS\Serializer\Annotation as JMS;

class Meta
{
    /** @JMS\Type("Butschster\Exchanger\Payloads\Request\Pagination") */
    public ?Pagination $pagination = null;
}
