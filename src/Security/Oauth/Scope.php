<?php
declare(strict_types=1);

namespace App\Security\Oauth;

use League\OAuth2\Server\Entities\ScopeEntityInterface;

class Scope implements ScopeEntityInterface
{
    private $identifier;

    public function __construct(string $identifier)
    {
        $this->identifier = $identifier;
    }

    public static function slugify(string $scope)
    {
        return 'SCOPE_' . strtoupper(preg_replace(['/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/'], ['\\1_\\2', '\\1_\\2'], $scope));
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function jsonSerialize()
    {
        return $this->identifier;
    }
}