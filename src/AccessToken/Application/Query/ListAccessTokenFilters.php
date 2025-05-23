<?php

namespace App\AccessToken\Application\Query;

use App\AccessToken\Domain\Contracts\RoleInterface;

class ListAccessTokenFilters
{
    private array $filters = [];

    public function withRole(RoleInterface $role): self
    {
        $this->setFilterValue('role', $role);

        return $this;
    }

    public function role(): ?RoleInterface
    {
        return $this->getFilterValue('role');
    }

    private function getFilterValue(string $name): mixed
    {
        return $this->filters[$name] ?? null;
    }

    private function setFilterValue(string $name, mixed $value): void
    {
        $this->filters[$name] = is_string($value) ? trim($value) : $value;
    }
}
