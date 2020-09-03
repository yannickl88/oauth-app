<?php
declare(strict_types=1);

namespace App\Form\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class EmailUnique extends Constraint
{
}