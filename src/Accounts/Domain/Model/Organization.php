<?php

namespace App\Accounts\Domain\Model;

class Organization
{
    public function __construct(
        public string $name,
        public string $cif,
        public string $organizationId
    ) {
    }

    public function getCif(): string
    {
        return $this->cif;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getOrganizationId(): string
    {
        return $this->organizationId;
    }
}
