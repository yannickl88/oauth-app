<?php
declare(strict_types=1);

namespace App\Struct;

use Symfony\Component\Validator\ConstraintViolationInterface;

class InvalidFieldStruct
{
    public $field;
    public $message;

    public static function fromViolation(ConstraintViolationInterface $violation): self
    {
        return new self($violation->getPropertyPath(), (string) $violation->getMessage());
    }

    public function __construct(string $field, string $message)
    {
        $this->field = $field;
        $this->message = $message;
    }
}