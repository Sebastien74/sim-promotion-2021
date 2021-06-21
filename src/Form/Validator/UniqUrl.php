<?php

namespace App\Form\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * UniqUrl
 *
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 *
 * @property string $message
 */
class UniqUrl extends Constraint
{
    public $message = "";
}