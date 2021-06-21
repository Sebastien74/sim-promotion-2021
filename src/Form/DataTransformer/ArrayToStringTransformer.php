<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * ArrayToStringTransformer
 *
 * Transform array to string
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ArrayToStringTransformer implements DataTransformerInterface
{
    /**
     * {@inheritDoc}
     */
    public function transform($array)
    {
        if (null === $array) {
            return '';
        }

        return implode(', ', $array);
    }

    /**
     * {@inheritDoc}
     */
    public function reverseTransform($string)
    {
        if (!$string) {
            return [];
        }

        return explode(', ', $string);
    }
}