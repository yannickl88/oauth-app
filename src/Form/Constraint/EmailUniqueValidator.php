<?php
declare(strict_types=1);

namespace App\Form\Constraint;

use App\Orm\Repository\UserRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class EmailUniqueValidator extends ConstraintValidator
{
    private $user_repository;

    public function __construct(UserRepository $user_repository)
    {
        $this->user_repository = $user_repository;
    }

    public function validate($value, Constraint $constraint)
    {
        if (null === $value || '' === $value) {
            return;
        }

        if (null !== $this->user_repository->findOneByEmail($value)) {
            $this->context->addViolation("Email is already in use by another user.");
        }
    }
}
