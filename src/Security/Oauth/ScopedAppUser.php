<?php
declare(strict_types=1);

namespace App\Security\Oauth;

use League\OAuth2\Server\Entities\ScopeEntityInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ScopedAppUser implements UserInterface
{
    private $base_user;
    private $roles;

    public function __construct(UserInterface $base_user, array $scopes)
    {
        $this->base_user = $base_user;
        $this->roles = array_merge($base_user->getRoles(), array_map(function (ScopeEntityInterface $scope) {
            return Scope::slugify($scope->getIdentifier());
        }, $scopes));
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function getPassword()
    {
        return $this->base_user->getPassword();
    }

    public function getSalt()
    {
        return $this->base_user->getSalt();
    }

    public function getUsername()
    {
        return $this->base_user->getUsername();
    }

    public function eraseCredentials()
    {
        $this->base_user->eraseCredentials();
    }
}