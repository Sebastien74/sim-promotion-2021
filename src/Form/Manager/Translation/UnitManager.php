<?php

namespace App\Form\Manager\Translation;

use App\Entity\Core\Website;
use App\Entity\Translation\Translation;
use App\Entity\Translation\TranslationUnit;
use App\Service\Translation\Extractor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * UnitManager
 *
 * Manage i18n Unit admin form
 *
 * @property Extractor $extractor
 * @property KernelInterface $kernel
 * @property EntityManagerInterface $entityManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class UnitManager
{
    private $extractor;
    private $kernel;
    private $entityManager;

    /**
     * UnitManager constructor.
     *
     * @param Extractor $extractor
     * @param KernelInterface $kernel
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(Extractor $extractor, KernelInterface $kernel, EntityManagerInterface $entityManager)
    {
        $this->extractor = $extractor;
        $this->kernel = $kernel;
        $this->entityManager = $entityManager;
    }

    /**
     * @prePersist
     *
     * @param TranslationUnit $translationUnit
     * @param Website $website
     */
    public function onFlush(TranslationUnit $translationUnit, Website $website)
    {
        $this->extractor->clearCache();
    }

    /**
     * Add Unit
     *
     * @param Form $form
     * @param Website $website
     */
    public function addUnit(Form $form, Website $website)
    {
        $existingUnit = $unit = $this->entityManager->getRepository(TranslationUnit::class)->findOneBy([
            'keyName' => $form->getData()['keyName'],
            'domain' => $form->getData()['domain']
        ]);

        if (!$unit) {
            $unit = new TranslationUnit();
            $unit->setDomain($form->getData()['domain']);
            $unit->setKeyname($form->getData()['keyName']);
            $this->entityManager->persist($unit);
        }

        foreach ($website->getConfiguration()->getAllLocales() as $locale) {

            $translation = $existingUnit ? $this->entityManager->getRepository(Translation::class)->findOneBy([
                'unit' => $unit,
                'locale' => $locale
            ]) : false;

            if (!$translation) {
                $translation = new Translation();
                $translation->setLocale($locale);
                $translation->setUnit($unit);
                $this->entityManager->persist($translation);
            }
        }

        $this->entityManager->flush();
        $this->extractor->clearCache();
    }
}