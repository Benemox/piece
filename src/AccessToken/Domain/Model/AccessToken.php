<?php

namespace App\AccessToken\Domain\Model;

use App\AccessToken\Domain\Contracts\RoleInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
#[ORM\Table(name: 'AccessToken')]
class AccessToken implements UserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'text', unique: true)]
    private string $token;

    #[ORM\Column(type: 'string', length: 50)]
    private string $role;

    private function __construct(string $token, string $role)
    {
        $this->token = $token;
        $this->role = $role;
    }

    public static function create(RoleInterface $role): self
    {
        return new self(self::generateSecureToken(), $role->getValue());
    }

    public static function createWithToken(string $token, RoleInterface $role): self
    {
        return new self($token, $role->getValue());
    }

    private static function generateSecureToken(): string
    {
        $bytes = random_bytes(200);
        $base64 = base64_encode($bytes);
        $base64UrlSafe = strtr($base64, '+/', '-_');

        return substr($base64UrlSafe, 0, 500);
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getRoles(): array
    {
        return [$this->role];
    }

    public function setRole(Role $role): void
    {
        $this->role = $role->getValue();
    }

    public function eraseCredentials(): void
    {
        // No se necesita borrar credenciales
    }

    public function getUserIdentifier(): string
    {
        return $this->token; // Symfony usa esto como identificador único
    }
}
