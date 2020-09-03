<?php
declare(strict_types=1);

namespace App\Struct;

use App\Form\Constraint\EmailUnique;
use Symfony\Component\Validator\Constraints as Assert;

class NewUserStruct
{
    /**
     * @Assert\Email()
     * @Assert\NotBlank()
     * @EmailUnique()
     */
    public $email;

    /**
     * @Assert\NotBlank()
     */
    public $password;

    public static function fromArray(array $data): self
    {
        return new self($data['email'], $data['password']);
    }

    public function __construct(string $email = null, string $password = null)
    {
        $this->email = $email;
        $this->password = $password;
    }
}
