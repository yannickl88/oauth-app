<?php
declare(strict_types=1);

namespace App\Orm\Oauth;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;

/**
 * @ORM\Entity(repositoryClass="App\Orm\Oauth\RefreshTokenRepository")
 * @ORM\Table("refresh_token")
 */
class RefreshToken implements RefreshTokenEntityInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     * @var string
     */
    private $identifier;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTimeImmutable|null
     */
    private $expiry_date;

    /**
     * @ORM\Column(type="boolean")
     * @var boolean
     */
    private $is_revoked = false;

    /**
     * @ORM\OneToOne(targetEntity="AccessToken")
     * @ORM\JoinColumn(referencedColumnName="identifier", name="access_token_identifier", nullable=false)
     * @var AccessToken
     */
    private $access_token;

    public function __construct(string $identifier, ?DateTimeImmutable $expiry_date, AccessToken $access_token)
    {
        $this->identifier = $identifier;
        $this->expiry_date = $expiry_date;
        $this->access_token = $access_token;
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

    public function setExpiryDateTime(DateTimeImmutable $date_time)
    {
        $this->expiry_date = $date_time;
    }

    public function setAccessToken(AccessTokenEntityInterface $access_token)
    {
        $this->access_token = $access_token;
    }

    public function getAccessToken(): AccessToken
    {
        return $this->access_token;
    }

    public function revoke(): void
    {
        $this->is_revoked = true;
    }

    public function isRevoked(): bool
    {
        return $this->is_revoked;
    }
}
