<?php

namespace App\Service\Admin;

use App\Entity\Core\Website;
use App\Entity\Layout\Block;
use App\Entity\Translation\i18n;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * TitleService
 *
 * Manage block title
 *
 * @property EntityManagerInterface $entityManager
 * @property Request $request
 * @property string $defaultLocale
 * @property bool $flush
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TitleService
{
    private $entityManager;
    private $request;
    private $defaultLocale;
    private $flush = false;

    /**
     * TitleService constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param RequestStack $requestStack
     */
    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack)
    {
        $this->entityManager = $entityManager;
        $this->request = $requestStack->getMasterRequest();
    }

    /**
     * Execute service
     *
     * @param Website $website
     */
    public function execute(Website $website)
    {
        try {
            $this->defaultLocale = $website->getConfiguration()->getLocale();
            $this->setEmptyTitleForce();
            if ($this->flush) {
                $this->entityManager->flush();
            }
        } catch (\Exception $exception) {
        }
    }

    /**
     * Set empty title force
     */
    private function setEmptyTitleForce()
    {
        if (!preg_match('/edit/', $this->request->getUri())) {

            $emptyTitlesForces = $this->getEmptyTitlesForces();

            if ($emptyTitlesForces) {

                $metasData = $this->entityManager->getMetadataFactory()->getAllMetadata();

                foreach ($metasData as $metaData) {

                    $classname = $metaData->getName();
                    $baseEntity = $metaData->getReflectionClass()->getModifiers() === 0 ? new $classname() : NULL;

                    if ($baseEntity && method_exists($baseEntity, 'getI18ns')) {
                        $entities = $this->entityManager->getRepository($classname)->findAll();
                        foreach ($entities as $entity) {
                            $titleForce = $this->getTitleForce($entity);
                            foreach ($entity->getI18ns() as $i18n) {
                                $this->setTitleForce($titleForce, $i18n);
                            }
                        }
                    } elseif ($baseEntity && method_exists($baseEntity, 'getI18n')) {

                        $entities = $this->entityManager->getRepository($classname)->findAll();

                        foreach ($entities as $entity) {
                            $this->setTitleForce(2, $entity->getI18n());
                        }
                    }
                }

                foreach ($emptyTitlesForces as $i18n) {
                    $this->setTitleForce(2, $i18n);
                }
            }
        }
    }

    /**
     * Get empty i18n[] with titleForce as NULL
     *
     * @return i18n[]
     */
    private function getEmptyTitlesForces(): array
    {
        return $this->entityManager->getRepository(i18n::class)
            ->createQueryBuilder('i')
            ->andWhere('i.titleForce IS NULL')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get title force
     *
     * @param $entity
     * @return int
     */
    private function getTitleForce($entity): int
    {
        $defaultTitleForce = NULL;
        $titleForce = $entity instanceof Block && $entity->getBlockType()->getSlug() === 'titleheader' ? 1 : 2;

        foreach ($entity->getI18ns() as $i18n) {
            if ($i18n->getLocale() === $this->defaultLocale && $i18n->getTitleForce()) {
                $defaultTitleForce = $i18n->getTitleForce();
            } elseif ($i18n->getTitleForce()) {
                $titleForce = $i18n->getTitleForce();
            }
        }

        return $defaultTitleForce ?: $titleForce;
    }

    /**
     * Set title force
     *
     * @param int $titleForce
     * @param i18n|null $i18n
     */
    private function setTitleForce(int $titleForce, i18n $i18n = NULL)
    {
        if ($i18n instanceof i18n && !$i18n->getTitleForce()) {
            $i18n->setTitleForce($titleForce);
            $this->entityManager->persist($i18n);
            $this->flush = true;
        }
    }
}