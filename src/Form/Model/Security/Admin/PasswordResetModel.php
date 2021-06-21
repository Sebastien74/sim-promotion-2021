<?php

namespace App\Form\Model\Security\Admin;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * PasswordResetModel
 *
 * Set User security asserts form attributes
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class PasswordResetModel
{
    /**
     * @Assert\NotBlank(message="Veuillez saisir un mot de passe.")
     * @Assert\Regex(
     *     message = "Le mot de passe doit comporter au moins 8 caractères, contenir au moins un chiffre, une majuscule et une minuscule.",
     *     pattern = "/(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9]).{8,}/"
     * )
     */
    public $plainPassword;
}