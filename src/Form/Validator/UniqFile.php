<?php

namespace App\Form\Validator;

use Doctrine\Common\Annotations\Annotation\Target;
use Symfony\Component\Validator\Constraint;

/**
 * UniqFile
 *
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 *
 * @property string $message
 */
class UniqFile extends Constraint
{
    public $message = "";
}