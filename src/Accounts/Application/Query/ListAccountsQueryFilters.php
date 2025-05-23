<?php

namespace App\Accounts\Application\Query;

use App\Shared\Application\QueryFilters;
use App\Shared\Domain\Model\Uid;

class ListAccountsQueryFilters extends QueryFilters
{
    public function withOrganizationId(string $organizationId): self
    {
        $this->setFilterValue('organizationId', $organizationId);

        return $this;
    }

    public function getOrganizationId(): ?Uid
    {
        if (null === $this->getFilterValue('organizationId')) {
            return null;
        }

        return Uid::cast($this->getFilterValue('organizationId'));
    }

    public function withCif(string $cif): self
    {
        $this->setFilterValue('cif', $cif);

        return $this;
    }

    public function getCif(): ?string
    {
        return $this->getFilterValue('cif');
    }
}
