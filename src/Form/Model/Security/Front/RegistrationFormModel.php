<?php

namespace App\Form\Model\Security\Front;

use App\Form\Validator\UniqUserEmail;
use App\Form\Validator\UniqUserLogin;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * RegistrationFormModel
 *
 * Set UserFront security asserts form attributes for registration
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class RegistrationFormModel
{
    /**
     * @Assert\NotBlank(message="Veuillez saisir un nom d'utilisateur.")
     * @UniqUserLogin()
     */
    public $login;

    /**
     * @Assert\NotBlank(message="Veuillez choisisir une langue.")
     */
    public $locale;

    /**
     * @Assert\NotBlank(message="Veuillez saisir un email.")
     * @Assert\Email()
     * @UniqUserEmail()
     */
    public $email;

    /**
     * @Assert\NotBlank(message="Veuillez saisir votre nom.")
     */
    public $lastName;

    /**
     * @Assert\NotBlank(message="Veuillez saisir votre prénom.")
     */
    public $firstName;

    /**
     * @Assert\NotBlank(message="Veuillez saisir un mot de passe.")
     * @Assert\Regex(
     *     message = "Le mot de passe doit comporter au moins 8 caractères, contenir au moins un chiffre, une majuscule et une minuscule.",
     *     pattern = "/(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9]).{8,}/"
     * )
     */
    public $plainPassword;

    /**
     * @Assert\IsTrue(message="Vous devez accepter les conditions générales.")
     */
    public $agreeTerms;

    /**
     * @Assert\Valid()
     */
    public $profile;
}