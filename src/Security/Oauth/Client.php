<?php
declare(strict_types=1);

namespace App\Security\Oauth;

use League\OAuth2\Server\Entities\ClientEntityInterface;

class Client implements ClientEntityInterface
{
    private $identifier;
    private $name;
    private $redirect_uri;
    private $is_confidential;

    public function __construct(string $identifier, string $name, string $redirect_uri, bool $is_confidential)
    {
        $this->identifier = $identifier;
        $this->name = $name;
        $this->redirect_uri = $redirect_uri;
        $this->is_confidential = $is_confidential;
    }

    public static function fromEntity(\App\Orm\Oauth\Client $client): self
    {
        return new self(
            $client->getIdentifier(),
            $client->getName(),
            $client->getRedirectUri(),
            $client->isConfidential()
        );
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getRedirectUri()
    {
        return $this->redirect_uri;
    }

    public function isConfidential()
    {
        return $this->is_confidential;
    }
}
