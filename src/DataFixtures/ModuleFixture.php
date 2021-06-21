<?php

namespace App\DataFixtures;

use App\Entity\Core\Module;
use App\Entity\Security\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * ModuleFixture
 *
 * Module Fixture management
 *
 * @property int $position
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ModuleFixture extends BaseFixture implements DependentFixtureInterface
{
    private $position = 1;

    /**
     * {@inheritDoc}
     */
    protected function loadData(ObjectManager $manager)
    {
        foreach ($this->getModules() as $config) {
            $module = $this->generateModule($config);
            $this->addReference($module->getSlug(), $module);
            $this->manager->persist($module);
        }

        $this->manager->flush();
    }

    /**
     * Generate BlockType
     *
     * @param array $config
     * @return Module
     */
    private function generateModule(array $config): Module
    {
        /** @var User $user */
        $user = $this->getReference('webmaster');

        $module = new Module();
        $module->setAdminName($config[0]);
        $module->setSlug($config[1]);
        $module->setRole($config[2]);
        $module->setIconClass($this->getIconPath($config[3]));
        $module->setPosition($this->position);
        $module->setCreatedBy($user);

        $this->position++;

        $this->manager->persist($module);

        return $module;
    }

    /**
     * Get Modules config
     *
     * @return array
     */
    private function getModules(): array
    {
        return [
            [$this->translator->trans('Pages', [], 'admin'), 'pages', 'ROLE_PAGE', 'fal fa-network-wired'],
            [$this->translator->trans('Google analytics', [], 'admin'), 'google-analytics', 'ROLE_GOOGLE_ANALYTICS', 'fal fa-chart-line'],
            [$this->translator->trans('CMS analytics <small>(Client)</small>', [], 'admin'), 'analytics-customer', 'ROLE_ANALYTICS', 'fal fa-chart-line'],
            [$this->translator->trans('CMS analytics <small>(Interne)</small>', [], 'admin'), 'analytics-internal', 'ROLE_ANALYTICS', 'fal fa-chart-line'],
            [$this->translator->trans('Informations', [], 'admin'), 'information', 'ROLE_INFORMATION', 'fal fa-info'],
            [$this->translator->trans('Formulaires', [], 'admin'), 'form', 'ROLE_FORM', 'fab fa-wpforms'],
            [$this->translator->trans('Calendriers de formulaire', [], 'admin'), 'form-calendar', 'ROLE_FORM_CALENDAR', 'fal fa-calendar-plus'],
            [$this->translator->trans('Formulaires à étapes', [], 'admin'), 'steps-form', 'ROLE_STEP_FORM', 'fab fa-wpforms'],
            [$this->translator->trans('Galeries', [], 'admin'), 'gallery', 'ROLE_GALLERY', 'fal fa-photo-video'],
            [$this->translator->trans('Médias', [], 'admin'), 'medias', 'ROLE_MEDIA', 'fal fa-photo-video'],
            [$this->translator->trans('Actualités', [], 'admin'), 'newscast', 'ROLE_NEWSCAST', 'fal fa-newspaper'],
            [$this->translator->trans('Navigations', [], 'admin'), 'navigation', 'ROLE_NAVIGATION', 'fal fa-bars'],
            [$this->translator->trans('Newsletters', [], 'admin'), 'newsletter', 'ROLE_NEWSLETTER', 'fal fa-typewriter'],
            [$this->translator->trans('Tableaux', [], 'admin'), 'table', 'ROLE_TABLE', 'fal fa-table'],
            [$this->translator->trans('FAQ', [], 'admin'), 'faq', 'ROLE_FAQ', 'fal fa-question'],
            [$this->translator->trans('Plan de site', [], 'admin'), 'sitemap', 'ROLE_SITE_MAP', 'fal fa-sitemap'],
            [$this->translator->trans('Cartes', [], 'admin'), 'map', 'ROLE_MAP', 'fal fa-map-marked'],
            [$this->translator->trans("Groupes d'onglets", [], 'admin'), 'tab', 'ROLE_TAB', 'fal fa-layer-group'],
            [$this->translator->trans('Moteurs de recherche', [], 'admin'), 'search', 'ROLE_SEARCH_ENGINE', 'fal fa-search'],
            [$this->translator->trans('RGPD', [], 'admin'), 'gdpr', 'ROLE_INTERNAL', 'fal fa-cookie'],
            [$this->translator->trans('Référencement', [], 'admin'), 'seo', 'ROLE_SEO', 'fal fa-chart-line'],
            [$this->translator->trans('Carrousels', [], 'admin'), 'slider', 'ROLE_SLIDER', 'fal fa-images'],
            [$this->translator->trans('Réalisations', [], 'admin'), 'making', 'ROLE_MAKING', 'fal fa-tools'],
            [$this->translator->trans('Portfolios', [], 'admin'), 'portfolio', 'ROLE_PORTFOLIO', 'fal fa-photo-video'],
            [$this->translator->trans('Évènements', [], 'admin'), 'event', 'ROLE_EVENT', 'fal fa-calendar-week'],
            [$this->translator->trans('Agendas', [], 'admin'), 'agenda', 'ROLE_AGENDA', 'fal fa-calendar-alt'],
            [$this->translator->trans('Catalogues', [], 'admin'), 'catalog', 'ROLE_CATALOG', 'fal fa-book-open'],
            [$this->translator->trans('Programmes immobiliers', [], 'admin'), 'real-estate-programs', 'ROLE_REAL_ESTATE_PROGRAM', 'fal fa-building'],
            [$this->translator->trans('Chronologies', [], 'admin'), 'timeline', 'ROLE_TIMELINE', 'fal fa-clock'],
            [$this->translator->trans('Forums', [], 'admin'), 'forum', 'ROLE_FORUM', 'fal fa-waveform'],
            [$this->translator->trans('Informations de contact', [], 'admin'), 'contact-information', 'ROLE_CONTACT', 'fal fa-info'],
            [$this->translator->trans('Traductions', [], 'admin'), 'translation', 'ROLE_TRANSLATION', 'fal fa-globe-stand'],
            [$this->translator->trans('Utilisateurs', [], 'admin'), 'user', 'ROLE_USERS', 'fal fa-users'],
            [$this->translator->trans('Actions personnalisées', [], 'admin'), 'customs-actions', 'ROLE_CUSTOMS_ACTIONS', 'fal fa-flame'],
            [$this->translator->trans('Pages sécurisées', [], 'admin'), 'secure-page', 'ROLE_SECURE_PAGE', 'fal fa-shield'],
            [$this->translator->trans('Modules sécurisés', [], 'admin'), 'secure-module', 'ROLE_SECURE_MODULE', 'fal fa-shield'],
            [$this->translator->trans('Classes personnalisées', [], 'admin'), 'css', 'ROLE_INTERNAL', 'fal fa-paint-brush'],
            [$this->translator->trans('Édition générale', [], 'admin'), 'edit', 'ROLE_EDIT', 'fal fa-pen-nib'],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getDependencies()
    {
        return [
            SecurityFixture::class
        ];
    }
}