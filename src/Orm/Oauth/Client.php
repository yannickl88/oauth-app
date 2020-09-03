<?php
declare(strict_types=1);

namespace App\Orm\Oauth;

use App\Security\Oauth\Scope;
use Doctrine\ORM\Mapping as ORM;
use League\OAuth2\Server\Entities\ScopeEntityInterface;

/**
 * @ORM\Entity(repositoryClass="App\Orm\Oauth\ClientRepository")
 * @ORM\Table("client")
 */
class Client
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $identifier;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\Column(type="string")
     */
    private $redirect_uri;

    /**
     * @ORM\Column(type="boolean")
     */
    private $is_confidential;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    private $secret;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $scopes;

    /**
     * @param string $identifier
     * @param string $name
     * @param string $redirect_uri
     * @param bool $is_confidential
     * @param string $secret
     * @param Scope[] $scopes
     */
    public function __construct(string $identifier, string $name, string $redirect_uri, bool $is_confidential, string $secret, array $scopes)
    {
        $this->identifier = $identifier;
        $this->name = $name;
        $this->redirect_uri = $redirect_uri;
        $this->is_confidential = $is_confidential;
        $this->secret = password_hash($secret, PASSWORD_DEFAULT);
        $this->scopes = implode(';', array_map(function (Scope $scope) {
            return $scope->getIdentifier();
        }, $scopes));
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRedirectUri()
    {
        return $this->redirect_uri;
    }

    public function isConfidential(): bool
    {
        return $this->is_confidential;
    }

    public function validate(?string $secret, ?string $grant_type): bool
    {
        if ($this->secret === null) {
            return true;
        }

        return password_verify((string) $secret, $this->secret);
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
}
