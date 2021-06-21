<?php

namespace App\Service\DataFixtures;

use App\Entity\Core\Security;
use App\Entity\Core\Website;
use App\Entity\Security\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

/**
 * SecurityFixture
 *
 * Security Configuration Fixture management
 *
 * @property EntityManagerInterface $entityManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SecurityFixture
{
    private $entityManager;

    /**
     * SecurityFixture constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Execute
     *
     * @param Website $website
     * @throws Exception
     */
    public function execute(Website $website)
    {
        $this->addWebsiteToMaster($website);
        $this->addConfiguration($website);
        $this->addWebsite($website);
    }

    /**
     * Add Configuration
     *
     * @param Website $website
     */
    private function addWebsiteToMaster(Website $website)
    {
        /** @var User $webmaster */
        $webmaster = $this->entityManager->getRepository(User::class)->findOneBy(['login' => 'webmaster']);
        if ($webmaster) {
            $webmaster->addWebsite($website);
            $this->entityManager->persist($webmaster);
        }
    }

    /**
     * Add Configuration
     *
     * @param Website $website
     * @throws Exception
     */
    private function addConfiguration(Website $website)
    {
        $security = new Security();
        $security->setWebsite($website);
        $security->setSecurityKey(crypt(random_bytes(30), 'rl'));

        $website->setSecurity($security);

        $this->entityManager->persist($security);
        $this->entityManager->persist($website);
    }

    /**
     * Add Website to customer
     *
     * @param Website $website
     */
    private function addWebsite(Website $website)
    {
        /** @var User $customer */
        $customer = $this->entityManager->getRepository(User::class)->findOneBy(['login' => 'customer']);

        if ($customer) {
            $customer->addWebsite($website);
            $this->entityManager->persist($customer);
        }
    }
}