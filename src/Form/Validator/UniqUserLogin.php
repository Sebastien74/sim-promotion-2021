<?php

namespace App\Form\Validator;

use Doctrine\Common\Annotations\Annotation\Target;
use Symfony\Component\Validator\Constraint;

/**
 * UniqUserLogin
 *
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 *
 * @property string $message
 */
class UniqUserLogin extends Constraint
{
    public $message = "";
}