<?php

namespace App\Shared\Domain\Contracts;

interface CustomExceptionInterface
{
    public function getCustomCode(): string;

    public function getDomain(): string;
}
