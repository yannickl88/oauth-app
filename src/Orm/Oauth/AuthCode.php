<?php
declare(strict_types=1);

namespace App\Orm\Oauth;

use App\Security\Oauth\Scope;
use Doctrine\ORM\Mapping as ORM;
use League\OAuth2\Server\Entities\ScopeEntityInterface;

/**
 * @ORM\Entity(repositoryClass="App\Orm\Oauth\AuthCodeRepository")
 * @ORM\Table("auth_code")
 */
class AuthCode
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
     * @var \DateTimeImmutable|null
     */
    private $expiry_date;

    /**
     * @ORM\Column(type="string")
     */
    private $redirect_uri;

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
     * AuthCode constructor.
     * @param string $identifier
     * @param string $user_identifier
     * @param \DateTimeImmutable|null $expiry_date
     * @param $redirect_uri
     * @param Client $client
     * @param ScopeEntityInterface[] $scopes
     */
    public function __construct(
        string $identifier,
        string $user_identifier,
        ?\DateTimeImmutable $expiry_date,
        string $redirect_uri,
        Client $client,
        array $scopes = []
    ) {
        $this->identifier = $identifier;
        $this->user_identifier = $user_identifier;
        $this->expiry_date = $expiry_date;
        $this->redirect_uri = $redirect_uri;
        $this->client = $client;
        $this->scopes = implode(';', array_map(function (ScopeEntityInterface $scope) {
            return $scope->getIdentifier();
        }, $scopes));
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function getExpiryDateTime(): \DateTimeImmutable
    {
        return $this->expiry_date;
    }

    public function getUserIdentifier(): string
    {
        return $this->user_identifier;
    }

    public function getRedirectUri()
    {
        return $this->redirect_uri;
    }

    /**
     * @return ScopeEntityInterface[]
     */
    public function getScopes(): array
    {
        if (empty($this->scopes)) {
            return [];
        }

        return array_map(function (string $scope) {
            return new Scope($scope);
        }, explode(';', $this->scopes));
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
