<?php
declare(strict_types=1);

namespace App\Security\Oauth;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

class ScopeRepository implements ScopeRepositoryInterface
{
    private const SCOPES = [
        'info',
        'parcel'
    ];

    private $client_repository;

    public function __construct(\App\Orm\Oauth\ClientRepository $client_repository)
    {
        $this->client_repository = $client_repository;
    }

    public function getScopeEntityByIdentifier($identifier)
    {
        if (!in_array($identifier, self::SCOPES, true)) {
            return null;
        }

        return new Scope($identifier);
    }

    public function finalizeScopes(array $scopes, $grant_type, ClientEntityInterface $client, $user_identifier = null)
    {
        if (null === ($client_entity = $this->client_repository->findByIdentifier($client->getIdentifier()))) {
            throw new \LogicException('Cannot find client.');
        }

        $allowed_scopes = array_map(function (ScopeEntityInterface $scope) {
            return $scope->getIdentifier();
        }, $client_entity->getScopes());

        return array_filter($scopes, function (ScopeEntityInterface $scope) use ($allowed_scopes) {
            return in_array($scope->getIdentifier(), $allowed_scopes);
        });
    }
}
