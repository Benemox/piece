<?php

namespace App\Transactions\Domain\Contracts;

interface RegistryInterface
{
    public function getRawData(): array;
}
