<?php

namespace Butschster\Exchanger\Payloads\Response;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use JMS\Serializer\Annotation as JMS;

class Headers
{
    /** @JMS\Type("Butschster\Exchanger\Payloads\Response\Meta") */
    public Meta $meta;

    public function paginate(LengthAwarePaginator $paginator)
    {
        $pagination = new Pagination();
        $pagination->total = $paginator->total();
        $pagination->perPage = $paginator->perPage();
        $pagination->currentPage = $paginator->currentPage();
        $pagination->totalPages = $paginator->lastPage();

        $this->meta->pagination = $pagination;
    }
}
