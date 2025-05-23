<?php

namespace App\Shared\Application;

class QueryFilters
{
    protected array $filters = [];

    protected function getFilterValue(string $name): mixed
    {
        return $this->filters[$name] ?? null;
    }

    protected function setFilterValue(string $name, mixed $value): void
    {
        $this->filters[$name] = is_string($value) ? trim($value) : $value;
    }
}
