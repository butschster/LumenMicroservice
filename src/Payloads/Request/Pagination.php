<?php

namespace Butschster\Exchanger\Payloads\Request;

use JMS\Serializer\Annotation as JMS;

class Pagination
{
    /** @JMS\Type("integer") */
    public int $page = 1;

    /** @JMS\Type("integer") */
    public int $limit = 25;
}
