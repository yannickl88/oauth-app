<?php
declare(strict_types=1);

namespace App\Security;

use App\Orm\Repository\UserRepository;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AppUserProvider implements UserProviderInterface
{
    private $user_repository;

    public function __construct(UserRepository $user_repository)
    {
        $this->user_repository = $user_repository;
    }

    public function loadUserByUsername($username)
    {
        if (null === ($user = $this->user_repository->findOneByEmail($username))) {
            throw new UsernameNotFoundException();
        }

        return AppUser::fromEntity($user);
    }

    public function refreshUser(UserInterface $user)
    {
        return $user;
    }

    public function supportsClass($class)
    {
        return $class === AppUser::class;
    }
}
