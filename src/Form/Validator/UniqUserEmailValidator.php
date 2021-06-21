<?php

namespace App\Form\Validator;

use App\Entity\Security\User;
use App\Form\Model\Security\Admin\RegistrationFormModel;
use App\Repository\Security\UserFrontRepository;
use App\Repository\Security\UserRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * UniqUserEmailValidator
 *
 * Check if User email already exist
 *
 * @property UserRepository $userRepository
 * @property UserFrontRepository $userFrontRepository
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class UniqUserEmailValidator extends ConstraintValidator
{
    private $userRepository;
    private $userFrontRepository;
    private $translator;

    /**
     * UniqUserEmailValidator constructor.
     *
     * @param UserRepository $userRepository
     * @param UserFrontRepository $userFrontRepository
     * @param TranslatorInterface $translator
     */
    public function __construct(UserRepository $userRepository, UserFrontRepository $userFrontRepository, TranslatorInterface $translator)
    {
        $this->userRepository = $userRepository;
        $this->userFrontRepository = $userFrontRepository;
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
        $repository = $user instanceof RegistrationFormModel || $user instanceof User ? $this->userRepository : $this->userFrontRepository;
        $existingUser = $repository->findOneBy(['email' => $value]);

        if (!$existingUser || $existingUser && method_exists($user, 'getId') && $existingUser->getId() === $user->getId()) {
            return;
        }

        $message = $this->translator->trans('Cet email existe déjà !', [], 'validators_cms');
        $this->context->buildViolation($message)
            ->addViolation();
    }
}