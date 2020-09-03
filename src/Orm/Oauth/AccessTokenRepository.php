<?php
declare(strict_types=1);

namespace App\Orm\Oauth;

use Doctrine\ORM\EntityRepository;

class AccessTokenRepository extends EntityRepository
{
    public function findByIdentifier(string $identifier): ?AccessToken
    {
        return $this->findOneBy(['identifier' => $identifier]);
    }
}
