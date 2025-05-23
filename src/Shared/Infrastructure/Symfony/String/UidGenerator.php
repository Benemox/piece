<?php

namespace App\Shared\Infrastructure\Symfony\String;

use App\Shared\Domain\Contracts\UidGeneratorInterface;
use App\Shared\Domain\Model\Uid;
use Symfony\Component\Uid\Factory\UuidFactory;

readonly class UidGenerator implements UidGeneratorInterface
{
    public function __construct(private UuidFactory $uuidFactory)
    {
    }

    public function generate(): Uid
    {
        return Uid::cast($this->uuidFactory->create());
    }
}
