<?php
declare(strict_types=1);

namespace App\Orm\Repository;

use App\Orm\Entity\User;
use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    public function findOneByEmail(string $email): ?User
    {
        /** @var User $user */
        $user = $this->findOneBy(['email' => $email]);

        return $user;
    }
}
