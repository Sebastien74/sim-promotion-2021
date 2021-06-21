<?php

namespace App\Form\Manager\Seo;

use App\Entity\Core\Website;
use Doctrine\ORM\EntityManagerInterface;

/**
 * RedirectionManager
 *
 * Manage admin Form form
 *
 * @property EntityManagerInterface $entityManager
 * @property bool $hasSSL
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class RedirectionManager
{
    private $entityManager;
    private $hasSSL;

    /**
     * RedirectionManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->hasSSL = $_ENV['PROTOCOL_' . strtoupper($_ENV['APP_ENV_NAME'])] === 'https';
    }

    /**
     * onFlush
     *
     * @param Website $website
     */
    public function onFlush(Website $website)
    {
        $this->setProtocol($website);
    }

    /**
     * Set Protocol in old URL
     *
     * @param Website $website
     */
    private function setProtocol(Website $website)
    {
        foreach ($website->getRedirections() as $redirection) {
            if(preg_match('/http:/', $redirection->getOld()) && $this->hasSSL) {
                $old = str_replace('http:', 'https:', $redirection->getOld());
                $redirection->setOld($old);
                $this->entityManager->persist($redirection);
                $this->entityManager->flush();
            }
        }
    }
}