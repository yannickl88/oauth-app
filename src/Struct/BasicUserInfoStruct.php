<?php
declare(strict_types=1);

namespace App\Struct;

use App\Orm\Entity\User;

class BasicUserInfoStruct
{
    public $id;
    public $house_number;
    public $house_number_addition;

    public static function fromUser(User $user): self
    {
        return new self($user->getId());
    }

    public function __construct(int $id)
    {
        $this->id = $id;
    }
}
