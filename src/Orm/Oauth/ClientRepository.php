<?php
declare(strict_types=1);

namespace App\Orm\Oauth;

use Doctrine\ORM\EntityRepository;

class ClientRepository extends EntityRepository
{
    public function findByIdentifier(string $identifier): ?Client
    {
        return $this->findOneBy(['identifier' => $identifier]);
    }
}
