<?php

namespace App\Service\DataFixtures;

use App\Entity\Module\Newsletter\Campaign;
use App\Entity\Core\Website;
use App\Entity\Security\User;
use App\Entity\Translation\i18n;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * NewsletterFixture
 *
 * Newsletter Fixture management
 *
 * @property EntityManagerInterface $entityManager
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class NewsletterFixture
{
    private $entityManager;
    private $translator;

    /**
     * NewsletterFixture constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param TranslatorInterface $translator
     */
    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    /**
     * Add Campaign
     *
     * @param Website $website
     * @param User|NULL $user
     * @throws Exception
     */
    public function add(Website $website, User $user = NULL)
    {
        $campaign = new Campaign();
        $campaign->setAdminName($this->translator->trans('Principale', [], 'admin'), 'main');
        $campaign->setWebsite($website);
        $campaign->setSlug('main');
        $campaign->setSecurityKey(crypt(random_bytes(10), 'rl'));

        $i18n = new i18n();
        $i18n->setLocale($website->getConfiguration()->getLocale());
        $i18n->setWebsite($website);
        $i18n->setIntroductionAlignment('text-center');
        $i18n->setIntroduction("En soumettant ce formulaire, vous acceptez que les informations saisies soient utilisées pour vous envoyer la newsletter de [nom du client]. Vous pourrez facilement vous désinscrire à tout moment via les liens de désinscription présents dans chacun de nos emails.");

        $campaign->addI18n($i18n);

        if ($user) {
            $campaign->setCreatedBy($user);
        }

        $this->entityManager->persist($campaign);
    }
}