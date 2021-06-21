<?php

namespace App\Form\Manager\Layout;

use App\Entity\Core\Website;
use App\Entity\Layout\Layout;
use App\Form\Manager\Core\BaseDuplicateManager;
use App\Repository\Layout\LayoutRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;

/**
 * LayoutDuplicateManager
 *
 * Manage admin Layout duplication form
 *
 * @property EntityManagerInterface $entityManager
 * @property LayoutRepository $repository
 * @property ZoneDuplicateManager $zoneDuplicateManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class LayoutDuplicateManager extends BaseDuplicateManager
{
    protected $entityManager;
    private $repository;
    private $zoneDuplicateManager;

    /**
     * LayoutDuplicateManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ZoneDuplicateManager $zoneDuplicateManager
     */
    public function __construct(EntityManagerInterface $entityManager, ZoneDuplicateManager $zoneDuplicateManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Layout::class);
        $this->zoneDuplicateManager = $zoneDuplicateManager;
    }

    /**
     * Execute duplication
     *
     * @param Layout $layout
     * @param Website $website
     * @param Form $form
     */
    public function execute(Layout $layout, Website $website, Form $form)
    {
    }

    /**
     * Duplicate Layout
     *
     * @param Layout $layout
     * @param Layout $layoutToDuplicate
     * @param Website $website
     */
    public function addLayout(Layout $layout, Layout $layoutToDuplicate, Website $website)
    {
        $layout->setWebsite($website);
        $layout->setPosition($this->getPosition($website));

        $this->entityManager->persist($layout);

        $this->zoneDuplicateManager->addZones($layout, $layoutToDuplicate->getZones(), $website);
    }

    /**
     * Get new position
     *
     * @param Website $website
     * @return int
     */
    private function getPosition(Website $website): int
    {
        return count($this->repository->findBy(['website' => $website])) + 1;
    }
}