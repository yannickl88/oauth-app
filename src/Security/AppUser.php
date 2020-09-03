<?php
declare(strict_types=1);

namespace App\Security;

use App\Orm\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;

class AppUser implements UserInterface
{
    private $username;
    private $password;
    private $roles = [];

    public static function fromEntity(User $user): self
    {
        return new self($user->getEmail(), $user->getAuthentication()->getPassword(), []);
    }

    public function __construct(string $username, string $password, array $roles)
    {
        $this->username = $username;
        $this->password = $password;
        $this->roles = $roles;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getSalt()
    {
        return null;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function eraseCredentials()
    {
        $this->password = null;
    }
}
