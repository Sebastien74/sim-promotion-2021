<?php

namespace App\Form\Manager\Gdpr;

use App\Entity\Core\Website;
use App\Entity\Gdpr\Cookie;
use Doctrine\ORM\EntityManagerInterface;

/**
 * CookieManager
 *
 * Manage Cookie admin form
 *
 * @property EntityManagerInterface $entityManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CookieManager
{
    private $entityManager;

    /**
     * CookieManager constructor.
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
     * @param Cookie $cookie
     * @param Website $website
     */
    public function prePersist(Cookie $cookie, Website $website)
    {
        $cookie->setAdminName($cookie->getCode());
        $cookie->setSlug($cookie->getCode());

        $this->entityManager->persist($cookie);
    }
}