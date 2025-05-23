<?php

namespace App\Shared\Domain\Exception;

interface ClientExceptionInterface
{
    public function getClientName(): string;
}
