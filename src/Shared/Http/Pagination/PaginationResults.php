<?php

namespace App\Shared\Http\Pagination;

class PaginationResults
{
    public int $pageNumber;

    public int $totalPages;

    public int $currentPageElements;

    public int $totalElements;

    public array $results = [];

    public function __construct(
        Page $page,
        int $totalElements,
        array $results
    ) {
        $this->pageNumber = $page->pageNumber();
        $this->totalPages = 0 === $totalElements ? 1 : (int) (ceil($totalElements / $page->limit()));
        $this->currentPageElements = count($results);
        $this->totalElements = $totalElements;
        $this->results = $results;
    }
}
