<?php

namespace App\Form\Validator;

use App\Entity\Security\User;
use App\Repository\Security\UserRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * UniqUserLoginValidator
 *
 * Check if User login already exist
 *
 * @property UserRepository $userRepository
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class UniqUserLoginValidator extends ConstraintValidator
{
    private $userRepository;
    private $translator;

    /**
     * UniqUserEmailValidator constructor.
     *
     * @param UserRepository $userRepository
     * @param TranslatorInterface $translator
     */
    public function __construct(UserRepository $userRepository, TranslatorInterface $translator)
    {
        $this->userRepository = $userRepository;
        $this->translator = $translator;
    }

    /**
     * Validate
     *
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        /* @var $constraint UniqUserEmail */

        /** @var User $user */
        $user = $this->context->getRoot()->getData();
        $existingUser = $this->userRepository->findOneBy(['login' => $value]);

        if (!$existingUser || $existingUser && $existingUser->getId() === $user->getId()) {
            return;
        }

        $message = $this->translator->trans('Cet identifiant existe déjà !', [], 'validators_cms');
        $this->context->buildViolation($message)
            ->addViolation();
    }
}