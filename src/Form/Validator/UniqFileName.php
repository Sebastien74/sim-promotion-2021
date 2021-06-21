<?php

namespace App\Form\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * UniqFileName
 *
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 *
 * @property string $message
 */
class UniqFileName extends Constraint
{
    public $message = "";
}