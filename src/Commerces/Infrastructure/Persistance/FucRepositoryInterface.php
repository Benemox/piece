<?php

namespace App\Commerces\Infrastructure\Persistance;

use App\Commerces\Domain\Model\Commerce;

interface FucRepositoryInterface
{
    public function findByCommerceId(string $commerceId): ?Commerce;
}
