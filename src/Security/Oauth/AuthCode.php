<?php
declare(strict_types=1);

namespace App\Security\Oauth;

use DateTimeImmutable;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;

class AuthCode implements AuthCodeEntityInterface
{
    private $redirect_uri;
    private $identifier;
    private $expiry_date;
    private $user_identifier;
    private $client;

    /**
     * @var ScopeEntityInterface[]
     */
    private $scopes = [];

    public function getRedirectUri()
    {
        return $this->redirect_uri;
    }

    public function setRedirectUri($uri)
    {
        $this->redirect_uri = $uri;
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