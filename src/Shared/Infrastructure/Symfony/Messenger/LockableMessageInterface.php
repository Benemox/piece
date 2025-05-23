<?php

namespace App\Shared\Infrastructure\Symfony\Messenger;

interface LockableMessageInterface
{
    public function getLockKey(): string;
}
