<?php
declare(strict_types=1);

namespace App\Security\Oauth;

use DateTimeImmutable;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;

class RefreshToken implements RefreshTokenEntityInterface
{
    private $identifier;
    private $expiry_date;
    /**
     * @var AccessTokenEntityInterface
     */
    private $access_token;

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

    public function setAccessToken(AccessTokenEntityInterface $access_token)
    {
        $this->access_token = $access_token;
    }

    public function getAccessToken()
    {
        return $this->access_token;
    }
}