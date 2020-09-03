<?php
declare(strict_types=1);

namespace App\Orm\Entity;

use Doctrine\ORM\Mapping as ORM;
use League\OAuth2\Server\Entities\ScopeEntityInterface;

/**
 * @ORM\Embeddable()
 */
class Authentication
{
    /**
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string")
     */
    private $approved_scopes;

    public function __construct(string $password)
    {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
        $this->approved_scopes = '';
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @param ScopeEntityInterface[] $scopes
     * @return bool
     */
    public function hasApproved(array $scopes): bool
    {
        $approved = empty($this->approved_scopes) ? [] : explode(';', $this->approved_scopes);

        foreach ($scopes as $scope) {
            if (!in_array($scope->getIdentifier(), $approved, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param ScopeEntityInterface[] $scopes
     */
    public function approveScopes(array $scopes): void
    {
        $this->approved_scopes = implode(';', array_map(function (ScopeEntityInterface $scope) {
            return $scope->getIdentifier();
        }, $scopes));
    }
}
