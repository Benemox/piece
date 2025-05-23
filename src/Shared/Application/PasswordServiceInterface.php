<?php

namespace App\Shared\Application;

interface PasswordServiceInterface
{
    public function getPasswordHash(string $plainPassword): string;
}
