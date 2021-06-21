<?php

namespace App\Form\Manager\Module;

use App\Entity\Module\Newsletter\Campaign;
use App\Entity\Core\Website;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

/**
 * CampaignManager
 *
 * Manage admin Newsletter form
 *
 * @property EntityManagerInterface $entityManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CampaignManager
{
    private $entityManager;

    /**
     * CampaignManager constructor.
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
     * @param Campaign $campaign
     * @param Website $website
     * @throws Exception
     */
    public function prePersist(Campaign $campaign, Website $website)
    {
        $campaign->setSecurityKey(crypt(random_bytes(10), 'rl'));

        $this->entityManager->persist($campaign);
    }
}