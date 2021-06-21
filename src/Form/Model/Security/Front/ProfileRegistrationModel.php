<?php

namespace App\Form\Model\Security\Front;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * ProfileRegistrationModel
 *
 * Set UserFront Profile security asserts form attributes for registration
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ProfileRegistrationModel
{
    /**
     * @Assert\NotBlank(message="Veuillez séléctionnez un genre.")
     */
    public $gender;
}