<?php
declare(strict_types=1);

namespace App\Security\Oauth;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

class ClientRepository implements ClientRepositoryInterface
{
    private $client_repository;

    public function __construct(
        \App\Orm\Oauth\ClientRepository $client_repository
    ) {
        $this->client_repository = $client_repository;
    }

    public function getClientEntity($identifier): ?ClientEntityInterface
    {
        if (null === ($client = $this->client_repository->findByIdentifier($identifier))) {
            return null;
        }

        return Client::fromEntity($client);
    }

    public function validateClient($identifier, $secret, $grant_type): bool
    {
        if (null === ($client = $this->client_repository->findByIdentifier($identifier))) {
            return false;
        }

        return $client->validate($secret, $grant_type);
    }
}
