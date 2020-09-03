<?php
declare(strict_types=1);

namespace App\Security\Oauth;

use App\Orm\Oauth\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;

class AccessTokenRepository implements AccessTokenRepositoryInterface
{
    private $access_token_repository;
    private $client_repository;
    private $entity_manager;

    public function __construct(
        \App\Orm\Oauth\AccessTokenRepository $access_token_repository,
        ClientRepository $client_repository,
        EntityManagerInterface $entity_manager
    ) {
        $this->access_token_repository = $access_token_repository;
        $this->client_repository = $client_repository;
        $this->entity_manager = $entity_manager;
    }

    public function getNewToken(ClientEntityInterface $client, array $scopes, $user_identifier = null)
    {
        $token = new AccessToken($client, $user_identifier);
        foreach ($scopes as $scope) {
            $token->addScope($scope);
        }

        return $token;
    }

    public function persistNewAccessToken(AccessTokenEntityInterface $access_token)
    {
        $client = $this->client_repository->findByIdentifier($access_token->getClient()->getIdentifier());

        $entity = new \App\Orm\Oauth\AccessToken(
            $access_token->getIdentifier(),
            $access_token->getUserIdentifier(),
            new \DateTime('@' . $access_token->getExpiryDateTime()->getTimestamp()),
            $client,
            $access_token->getScopes()
        );

        $this->entity_manager->persist($entity);
        $this->entity_manager->flush();
    }

    public function revokeAccessToken($code_id)
    {
        /* @var \App\Orm\Oauth\AccessToken $token */
        if (null === ($token = $this->access_token_repository->find($code_id))) {
            return;
        }

        $token->revoke();
        $this->entity_manager->flush();
    }

    public function isAccessTokenRevoked($code_id)
    {
        /* @var \App\Orm\Oauth\AccessToken $token */
        if (null === ($token = $this->access_token_repository->find($code_id))) {
            return true;
        }

        return $token->isRevoked();
    }

    public function findByIdentifier($code_id): ?AccessToken
    {
        /* @var \App\Orm\Oauth\AccessToken $token */
        if (null === ($token = $this->access_token_repository->find($code_id))) {
            return null;
        }

        return AccessToken::fromEntity($token);
    }
}
