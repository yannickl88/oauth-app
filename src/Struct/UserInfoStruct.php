<?php
declare(strict_types=1);

namespace App\Struct;

use App\Orm\Entity\User;

class UserInfoStruct
{
    public $id;
    public $email;

    public static function fromUser(User $user): self
    {
        return new self($user->getId(), $user->getEmail());
    }

    public function __construct(int $id, string $email)
    {
        $this->id = $id;
        $this->email = $email;
    }
}
