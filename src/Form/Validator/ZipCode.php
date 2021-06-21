<?php

namespace App\Form\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * ZipCode
 *
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 *
 * @property string $message
 */
class ZipCode extends Constraint
{
    public $message = "";
}