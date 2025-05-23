<?php

namespace App\Shared\Infrastructure\Symfony\Messenger;

use Symfony\Component\Messenger\Stamp\StampInterface;

class UniqueIdStamp implements StampInterface
{
    private string $uniqueId;

    public function __construct()
    {
        $this->uniqueId = uniqid('', true);
    }

    public function getUniqueId(): string
    {
        return $this->uniqueId;
    }
}
