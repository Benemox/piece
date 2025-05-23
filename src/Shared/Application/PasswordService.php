<?php

namespace App\Shared\Application;

use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

class PasswordService implements PasswordServiceInterface
{
    private PasswordHasherInterface $passwordHasher;

    public function __construct()
    {
        $factory = new PasswordHasherFactory([
            'default' => ['algorithm' => 'auto'],
        ]);
        $this->passwordHasher = $factory->getPasswordHasher('default');
    }

    public function getPasswordHash(string $plainPassword): string
    {
        return $this->passwordHasher->hash($plainPassword);
    }
}
