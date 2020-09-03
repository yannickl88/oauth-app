<?php
declare(strict_types=1);

namespace App\Security\Oauth;

use League\OAuth2\Server\Entities\UserEntityInterface;

class User implements UserEntityInterface
{
    private $identifier;

    public function __construct(string $identifier)
    {
        $this->identifier = $identifier;
    }

    public static function fromEntity(\App\Orm\Oauth\User $user): self
    {
        return new self((string) $user->getEmail());
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }
}
