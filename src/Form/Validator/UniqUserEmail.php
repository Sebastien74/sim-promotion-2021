<?php

namespace App\Form\Validator;

use Doctrine\Common\Annotations\Annotation\Target;
use Symfony\Component\Validator\Constraint;

/**
 * UniqUserEmail
 *
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 *
 * @property string $message
 */
class UniqUserEmail extends Constraint
{
    public $message = "";
}
