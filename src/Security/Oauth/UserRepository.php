<?php
declare(strict_types=1);

namespace App\Security\Oauth;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    private $user_repository;

    public function __construct(\App\Orm\Repository\UserRepository $user_repository)
    {
        $this->user_repository = $user_repository;
    }

    public function getUserEntityByUserCredentials($username, $password, $grant_type, ClientEntityInterface $client)
    {
        if (null === ($user = $this->user_repository->findOneByEmail($username))) {
            return null;
        }

        if (!password_verify($password, $user->getAuthentication()->getPassword())) {
            return null;
        }

        return User::fromEntity($user);
    }
}
