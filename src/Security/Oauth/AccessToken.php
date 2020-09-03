<?php
declare(strict_types=1);

namespace App\Security\Oauth;

use DateTimeImmutable;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;

class AccessToken implements AccessTokenEntityInterface
{
    /**
     * @var CryptKey
     */
    private $private_key;
    private $identifier;
    private $expiry_date;
    private $user_identifier;
    private $client;
    /**
     * @var ScopeEntityInterface[]
     */
    private $scopes = [];

    public function __construct(ClientEntityInterface $client, ?string $user_identifier)
    {
        $this->client = $client;
        $this->user_identifier = $user_identifier;
    }

    public static function fromEntity(\App\Orm\Oauth\AccessToken $token): self
    {
        $self = new self(Client::fromEntity($token->getClient()), $token->getUserIdentifier());

        $self->setExpiryDateTime(DateTimeImmutable::createFromMutable($token->getExpiryDateTime()));
        $self->setIdentifier($token->getIdentifier());

        foreach ($token->getScopes() as $identifier) {
            $self->addScope(new Scope($identifier));
        }

        return $self;
    }

    public function setPrivateKey(CryptKey $private_key)
    {
        $this->private_key = $private_key;
    }

    public function __toString()
    {
        return $this->getIdentifier();
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    public function getExpiryDateTime()
    {
        return $this->expiry_date;
    }

    public function setExpiryDateTime(DateTimeImmutable $date)
    {
        $this->expiry_date = $date;
    }

    public function setUserIdentifier($identifier)
    {
        $this->user_identifier = $identifier;
    }

    public function getUserIdentifier()
    {
        return $this->user_identifier;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function setClient(ClientEntityInterface $client)
    {
        $this->client = $client;
    }

    public function addScope(ScopeEntityInterface $scope)
    {
        $this->scopes[] = $scope;
    }

    public function getScopes()
    {
        return $this->scopes;
    }
}
