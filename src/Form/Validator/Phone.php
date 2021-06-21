<?php

namespace App\Form\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Phone
 *
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 *
 * @property string $message
 */
class Phone extends Constraint
{
    public $message = "";
}