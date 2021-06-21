<?php

namespace App\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * SwitchToUserVoter
 *
 * @property Security $security
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SwitchToUserVoter extends Voter
{
    private $security;

    /**
     * SwitchToUserVoter constructor.
     *
     * @param Security $security
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ['CAN_SWITCH_USER'])
            && $subject instanceof UserInterface;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        // If the user is anonymous or if the subject is not a user, do not grant access
        if (!$user instanceof UserInterface || !$subject instanceof UserInterface) {
            return false;
        }

        // Disable switch to ROLE_INTERNAL
        if (in_array('ROLE_INTERNAL', $subject->getRoles())) {
            return false;
        }

        // All switch only for ROLE_ALLOWED_TO_SWITCH
        if ($this->security->isGranted('ROLE_ALLOWED_TO_SWITCH')) {
            return true;
        }

        return false;
    }
}