<?php

namespace App\Form\DataTransformer;

use App\Entity\Security\User;
use App\Repository\Security\UserRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * EmailToUserTransformer
 *
 * Transform Email to User
 *
 * @property UserRepository $userRepository
 * @property callable $finderCallback
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class EmailToUserTransformer implements DataTransformerInterface
{
    private $userRepository;
    private $finderCallback;

    /**
     * EmailToUserTransformer constructor.
     *
     * @param UserRepository $userRepository
     * @param callable $finderCallback
     */
    public function __construct(UserRepository $userRepository, callable $finderCallback)
    {
        $this->userRepository = $userRepository;
        $this->finderCallback = $finderCallback;
    }

    /**
     * {@inheritDoc}
     */
    public function transform($value)
    {
        if (null === $value) {
            return '';
        }

        if (!$value instanceof User) {
            throw new \LogicException('The UserEmailSelectType can only be used with User objects');
        }

        return $value->getEmail();
    }

    /**
     * {@inheritDoc}
     */
    public function reverseTransform($value)
    {
        if (!$value) {
            return;
        }

        $callback = $this->finderCallback;
        $user = $callback($this->userRepository, $value);

        if (!$user) {
            throw new TransformationFailedException(sprintf(
                'No user found with email "%s"',
                $value
            ));
        }

        return $user;
    }
}