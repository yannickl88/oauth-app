<?php
declare(strict_types=1);

namespace App\Security\Oauth;

use App\Orm\Oauth\AuthCodeRepository as EntityAuthCodeRepository;
use App\Orm\Oauth\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;

class AuthCodeRepository implements AuthCodeRepositoryInterface
{
    private $auth_code_repository;
    private $client_repository;
    private $entity_manager;

    public function __construct(
        EntityAuthCodeRepository $auth_code_repository,
        ClientRepository $client_repository,
        EntityManagerInterface $entity_manager
    ) {
        $this->auth_code_repository = $auth_code_repository;
        $this->client_repository = $client_repository;
        $this->entity_manager = $entity_manager;
    }

    public function getNewAuthCode()
    {
        return new AuthCode();
    }

    public function persistNewAuthCode(AuthCodeEntityInterface $auth_code)
    {
        $client = $this->client_repository->findByIdentifier($auth_code->getClient()->getIdentifier());

        $entity = new \App\Orm\Oauth\AuthCode(
            $auth_code->getIdentifier(),
            $auth_code->getUserIdentifier(),
            $auth_code->getExpiryDateTime(),
            $auth_code->getRedirectUri(),
            $client,
            $auth_code->getScopes()
        );

        $this->entity_manager->persist($entity);
        $this->entity_manager->flush();
    }

    public function revokeAuthCode($code_id)
    {
        /* @var \App\Orm\Oauth\AuthCode $token */
        if (null === ($token = $this->auth_code_repository->find($code_id))) {
            return;
        }

        $token->revoke();
        $this->entity_manager->flush();
    }

    public function isAuthCodeRevoked($code_id)
    {
        /* @var \App\Orm\Oauth\AuthCode $token */
        if (null === ($token = $this->auth_code_repository->find($code_id))) {
            return true;
        }

        return $token->isRevoked();
    }
}
