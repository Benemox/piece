<?php

namespace App\Shared\Http\Pagination;

class Page
{
    public static function default(): self
    {
        return new self(1, 10);
    }

    public function __construct(private int $number, private int $results)
    {
        assert($number > 0);
        assert($results > 0);
    }

    public function pageNumber(): int
    {
        return $this->number;
    }

    public function limit(): int
    {
        return $this->results;
    }

    public function offset(): int
    {
        return $this->results * ($this->number - 1);
    }
}
