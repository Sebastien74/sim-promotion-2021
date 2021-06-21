<?php

namespace App\Form\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * UniqDate
 *
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 */
class UniqDate extends Constraint
{
    public $message = "";
}