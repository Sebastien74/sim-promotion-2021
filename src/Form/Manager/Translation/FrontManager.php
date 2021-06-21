<?php

namespace App\Form\Manager\Translation;

use App\Entity\Core\Domain;
use App\Entity\Translation\Translation;
use App\Entity\Translation\TranslationDomain;
use App\Entity\Translation\TranslationUnit;
use App\Repository\Translation\TranslationDomainRepository;
use App\Repository\Translation\TranslationRepository;
use App\Repository\Translation\TranslationUnitRepository;
use App\Service\Translation\Extractor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * i18nManager
 *
 * Manage i18n admin form
 *
 * @property Request $request
 * @property KernelInterface $kernel
 * @property TranslatorInterface $translator
 * @property EntityManagerInterface $entityManager
 * @property TranslationDomainRepository $domainRepository
 * @property TranslationUnitRepository $unitRepository
 * @property TranslationRepository $translationRepository
 * @property Extractor $extractor
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FrontManager
{
    private $request;
    private $kernel;
    private $translator;
    private $entityManager;
    private $domainRepository;
    private $unitRepository;
    private $translationRepository;
    private $extractor;

    /**
     * FrontManager constructor.
     *
     * @param RequestStack $requestStack
     * @param KernelInterface $kernel
     * @param TranslatorInterface $translator
     * @param EntityManagerInterface $entityManager
     * @param TranslationDomainRepository $domainRepository
     * @param TranslationUnitRepository $unitRepository
     * @param TranslationRepository $translationRepository
     * @param Extractor $extractor
     */
    public function __construct(
        RequestStack $requestStack,
        KernelInterface $kernel,
        TranslatorInterface $translator,
        EntityManagerInterface $entityManager,
        TranslationDomainRepository $domainRepository,
        TranslationUnitRepository $unitRepository,
        TranslationRepository $translationRepository,
        Extractor $extractor)
    {
        $this->request = $requestStack->getMasterRequest();
        $this->kernel = $kernel;
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->domainRepository = $domainRepository;
        $this->unitRepository = $unitRepository;
        $this->translationRepository = $translationRepository;
        $this->extractor = $extractor;
    }

    /**
     * Process to post text (|trans)
     */
    public function postText(): array
    {
        $domain = $this->getDomain();
        $translation = $this->getTranslation($domain);
        $message = NULL;

        if (!$translation) {
            $message = $this->translator->trans("Deux contenus identiques ont été trouvés. Veuillez éditer via l'administration.", [], 'admin');
        } else {

            $translation->setContent($this->request->get('content'));
            $this->entityManager->flush();

            /** Cache clear */
            $this->extractor->clearCache();
        }

        return ['success' => $translation !== false, 'message' => $message, 'content' => $this->request->get('content')];
    }

    /**
     * Get TranslationDomain
     *
     * @return TranslationDomain
     */
    private function getDomain(): TranslationDomain
    {
        $domain = $this->domainRepository->findOneBy(['name' => $this->request->get('domain')]);

        if (!$domain) {
            $domain = new TranslationDomain();
            $domain->setAdminName($this->request->get('domain'));
            $domain->setName($this->request->get('domain'));
            $this->entityManager->persist($domain);
            $this->entityManager->flush();
        }

        return $domain;
    }

    /**
     * Get Translation
     *
     * @param TranslationDomain $domain
     * @return Translation|bool
     */
    private function getTranslation(TranslationDomain $domain)
    {
        $translations = $this->translationRepository->findByDomainAndKeyName($domain, $this->request->get('key_name'), $this->request->get('locale'));

        if (count($translations) > 1) {
            return false;
        }

        $translation = !empty($translations[0]) ? $translations[0] : NULL;

        if (!$translations) {
            $translation = new Translation();
            $translation->setLocale($this->request->get('locale'));
            $translation->setUnit($this->getUnit($domain));
            $this->entityManager->persist($translation);
        }

        return $translation;
    }

    /**
     * Get TranslationUnit
     *
     * @param TranslationDomain $domain
     * @return TranslationUnit
     */
    private function getUnit(TranslationDomain $domain): TranslationUnit
    {
        $unit = new TranslationUnit();
        $unit->setDomain($domain);
        $unit->setKeyname($this->request->get('key_name'));
        $this->entityManager->persist($unit);

        return $unit;
    }
}