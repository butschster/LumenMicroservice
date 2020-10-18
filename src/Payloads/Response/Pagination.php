<?php

namespace Butschster\Exchanger\Payloads\Response;

use JMS\Serializer\Annotation as JMS;

class Pagination
{
    /** @JMS\Type("integer") */
    public int $total = 0;

    /** @JMS\Type("integer") */
    public int $perPage;

    /** @JMS\Type("integer") */
    public int $currentPage = 1;

    /** @JMS\Type("integer") */
    public int $totalPages;
}
