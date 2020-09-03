<?php
declare(strict_types=1);

namespace App\Orm\Oauth;

use Doctrine\ORM\Mapping as ORM;
use League\OAuth2\Server\Entities\ScopeEntityInterface;

/**
 * @ORM\Entity(repositoryClass="App\Orm\Oauth\AccessTokenRepository")
 * @ORM\Table("access_token")
 */
class AccessToken
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     * @var string
     */
    private $identifier;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $user_identifier;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime|null
     */
    private $expiry_date;

    /**
     * @ORM\Column(type="boolean")
     * @var boolean
     */
    private $is_revoked = false;

    /**
     * @ORM\ManyToOne(targetEntity="Client")
     * @ORM\JoinColumn()
     * @var Client
     */
    private $client;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $scopes;

    /**
     * @param string $identifier
     * @param string $user_identifier
     * @param \DateTime|null $expiry_date
     * @param Client $client
     * @param ScopeEntityInterface[] $scopes
     */
    public function __construct(
        string $identifier,
        string $user_identifier,
        ?\DateTime $expiry_date,
        Client $client,
        array $scopes
    ) {
        $this->identifier = $identifier;
        $this->user_identifier = $user_identifier;
        $this->expiry_date = $expiry_date;
        $this->client = $client;
        $this->scopes = implode(';', array_map(function (ScopeEntityInterface $scope) {
            return $scope->getIdentifier();
        }, $scopes));
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function getExpiryDateTime(): \DateTime
    {
        return $this->expiry_date;
    }

    public function getUserIdentifier(): string
    {
        return $this->user_identifier;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function revoke(): void
    {
        $this->is_revoked = true;
    }

    public function isRevoked(): bool
    {
        return $this->is_revoked;
    }

    public function getScopes(): array
    {
        if (empty($this->scopes)) {
            return [];
        }

        return explode(';', $this->scopes);
    }
}
