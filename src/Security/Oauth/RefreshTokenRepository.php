<?php
declare(strict_types=1);

namespace App\Security\Oauth;

use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    private $refresh_token_repository;
    private $access_token_repository;
    private $entity_manager;

    public function __construct(
        \App\Orm\Oauth\RefreshTokenRepository $refresh_token_repository,
        \App\Orm\Oauth\AccessTokenRepository $access_token_repository,
        EntityManagerInterface $entity_manager
    ) {
        $this->refresh_token_repository = $refresh_token_repository;
        $this->access_token_repository = $access_token_repository;
        $this->entity_manager = $entity_manager;
    }

    public function getNewRefreshToken()
    {
        return new RefreshToken();
    }

    public function persistNewRefreshToken(RefreshTokenEntityInterface $token)
    {
        $access_token = $this->access_token_repository->findByIdentifier($token->getAccessToken()->getIdentifier());

        $entity = new \App\Orm\Oauth\RefreshToken(
            $token->getIdentifier(),
            $token->getExpiryDateTime(),
            $access_token
        );

        $this->entity_manager->persist($entity);
        $this->entity_manager->flush();
    }

    public function revokeRefreshToken($token_id)
    {
        /* @var \App\Orm\Oauth\AuthCode $token */
        if (null === ($token = $this->refresh_token_repository->find($token_id))) {
            return;
        }

        $token->revoke();
        $this->entity_manager->flush();
    }

    public function isRefreshTokenRevoked($token_id)
    {
        /* @var \App\Orm\Oauth\RefreshToken $token */
        if (null === ($token = $this->refresh_token_repository->find($token_id))) {
            return true;
        }

        return $token->isRevoked();
    }
}
