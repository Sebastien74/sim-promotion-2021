<?php

namespace App\Form\Validator;

use Symfony\Component\Validator\Constraints\File as FileConstraint;

/**
 * File
 *
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 *
 * @property int $maxSize
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class File extends FileConstraint
{

}