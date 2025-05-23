<?php

namespace App\Shared\Domain\Contracts;

use App\Shared\Domain\Model\Uid;

interface UidGeneratorInterface
{
    public function generate(): Uid;
}
