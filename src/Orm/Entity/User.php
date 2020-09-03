<?php
declare(strict_types=1);

namespace App\Orm\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Orm\Repository\UserRepository")
 * @ORM\Table("user")
 */
class User
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $email;

    /**
     * @ORM\Embedded(class="Authentication")
     */
    private $authentication;

    public function __construct(string $email, Authentication $authentication)
    {
        $this->email = $email;
        $this->authentication = $authentication;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getAuthentication(): Authentication
    {
        return $this->authentication;
    }
}
