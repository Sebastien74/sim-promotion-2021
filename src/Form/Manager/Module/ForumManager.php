<?php

namespace App\Form\Manager\Module;

use App\Entity\Module\Forum\Forum;
use App\Entity\Core\Website;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

/**
 * ForumManager
 *
 * Manage admin Forum form
 *
 * @property EntityManagerInterface $entityManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ForumManager
{
    private $entityManager;

    /**
     * ForumManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @prePersist
     *
     * @param Forum $forum
     * @param Website $website
     * @throws Exception
     */
    public function prePersist(Forum $forum, Website $website)
    {
        $forum->setSecurityKey(crypt(random_bytes(10), 'rl'));

        $this->entityManager->persist($forum);
    }
}