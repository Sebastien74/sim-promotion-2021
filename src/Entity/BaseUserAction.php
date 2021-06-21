<?php

namespace App\Entity;

use App\Entity\Security\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * BaseUserAction
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
abstract class BaseUserAction
{
    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Security\User", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $createdBy;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Security\User", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $updatedBy;

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $user = $this->getCurrentUser();
        if ($user && $user instanceof User) {
            $this->createdBy = $user;
        }
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $user = $this->getCurrentUser();
        if ($user && $user instanceof User) {
            $this->updatedBy = $user;
        }
    }

    /**
     *  To get the current User
     *
     * @return User|string
     */
    public function getCurrentUser()
    {
        global $kernel;

        $currentUser = NULL;
        $container = $kernel->getContainer();
        $token = $container->get('security.token_storage')->getToken();

        if (method_exists($token, 'getUser') && method_exists($token->getUser(), 'getId')) {
            $currentUser = $token->getUser();
        }

        if ($currentUser === 'anon.' || !empty($currentUser) && $currentUser->getUsername() === "debug") {
            $currentUser = NULL;
        }

        return $currentUser;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getUpdatedBy(): ?User
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?User $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }
}