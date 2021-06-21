<?php

namespace App\DataFixtures;

use App\Controller\Front\Action as Controller;
use App\Entity\Module\Agenda\Agenda;
use App\Entity\Module\Catalog as CatalogEntities;
use App\Entity\Module\Contact\Contact;
use App\Entity\Module\Faq\Faq;
use App\Entity\Module\Form\Form;
use App\Entity\Module\Form\StepForm;
use App\Entity\Module\Forum\Forum;
use App\Entity\Module\Gallery\Gallery;
use App\Entity\Module\Map\Map;
use App\Entity\Module\Menu\Menu;
use App\Entity\Module\Event as EventEntities;
use App\Entity\Module\Making as MakingEntities;
use App\Entity\Module\Newscast as NewscastEntities;
use App\Entity\Module\Portfolio as PortfolioEntities;
use App\Entity\Module\Search\Search;
use App\Entity\Module\Slider\Slider;
use App\Entity\Module\Tab\Tab;
use App\Entity\Module\Table\Table;
use App\Entity\Module\Timeline\Timeline;
use App\Entity\Core\Module;
use App\Entity\Layout\Action;
use App\Entity\Security\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * ActionFixture
 *
 * Action Fixture management
 *
 * @property int $position
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ActionFixture extends BaseFixture implements DependentFixtureInterface
{
    private $position = 1;

    /**
     * {@inheritDoc}
     */
    protected function loadData(ObjectManager $manager)
    {
        foreach ($this->getActions() as $config) {
            $action = $this->addAction($config);
            $this->addReference($action->getSlug(), $action);
        }
    }

    /**
     * Generate Action
     *
     * @param array $config
     * @return Action
     */
    private function addAction(array $config): Action
    {
        /** @var User $user */
        $user = $this->getReference('webmaster');

        /** @var Module $module */
        $module = $this->getReference($config[6]);

        $action = new Action();
        $action->setAdminName($config[0]);
        $action->setController($config[1]);
        $action->setAction($config[2]);
        $action->setEntity($config[3]);
        $action->setSlug($config[4]);
        $action->setIconClass($this->getIconPath($config[5]));
        $action->setModule($module);
        $action->setDropdown(!empty($config[7]));
        $action->setPosition($this->position);
        $action->setCreatedBy($user);

        $this->position++;
        $this->manager->persist($action);
        $this->manager->flush();

        return $action;
    }

    /**
     * Get Actions config
     *
     * @return array
     */
    private function getActions(): array
    {
        return [
            [$this->translator->trans('Carrousel', [], 'admin'), Controller\SliderController::class, 'view', Slider::class, 'slider-view', 'fal fa-images', 'slider'],
            [$this->translator->trans('Galerie', [], 'admin'), Controller\GalleryController::class, 'view', Gallery::class, 'gallery-view', 'fal fa-photo-video', 'gallery', true],
            [$this->translator->trans('Formulaire', [], 'admin'), Controller\FormController::class, 'view', Form::class, 'form-view', 'fab fa-wpforms', 'form'],
            [$this->translator->trans('Formulaire page de succès', [], 'admin'), Controller\FormController::class, 'success', Form::class, 'form-success', 'fal fa-ballot-check', 'form', true],
            [$this->translator->trans('Calendrier de formulaire', [], 'admin'), Controller\FormController::class, 'calendar', NULL, 'form-calendar-view', 'fal fa-calendar-plus', 'form-calendar', true],
            [$this->translator->trans('Formulaire à étapes', [], 'admin'), Controller\FormController::class, 'step', StepForm::class, 'form-step', 'fab fa-wpforms', 'steps-form', true],
            [$this->translator->trans("Teaser d'actualités", [], 'admin'), Controller\NewscastController::class, 'teaser', NewscastEntities\Teaser::class, 'newscast-teaser', 'fal fa-newspaper', 'newscast'],
            [$this->translator->trans("Liste d'actualités", [], 'admin'), Controller\NewscastController::class, 'index', NewscastEntities\Listing::class, 'newscast-index', 'fal fa-newspaper', 'newscast'],
            [$this->translator->trans("Teaser portfolio", [], 'admin'), Controller\PortfolioController::class, 'teaser', PortfolioEntities\Teaser::class, 'portfolio-teaser', 'fal fa-photo-video', 'portfolio', true],
            [$this->translator->trans("Portfolio", [], 'admin'), Controller\PortfolioController::class, 'index', PortfolioEntities\Listing::class, 'portfolio-index', 'fal fa-photo-video', 'portfolio', true],
            [$this->translator->trans("Teaser de réalisations", [], 'admin'), Controller\MakingController::class, 'teaser', MakingEntities\Teaser::class, 'making-teaser', 'fal fa-tools', 'making', true],
            [$this->translator->trans("Liste des réalisations", [], 'admin'), Controller\MakingController::class, 'index', MakingEntities\Listing::class, 'making-index', 'fal fa-list-alt', 'making', true],
            [$this->translator->trans('Menu', [], 'admin'), Controller\MenuController::class, 'view', Menu::class, 'menu-view', 'fal fa-bars', 'navigation', true],
            [$this->translator->trans('Plan de site', [], 'admin'), Controller\SitemapController::class, 'view', NULL, 'sitemap-view', 'fal fa-sitemap', 'sitemap', true],
            [$this->translator->trans('Tableau', [], 'admin'), Controller\TableController::class, 'view', Table::class, 'table-view', 'fal fa-table', 'table'],
            [$this->translator->trans('FAQ', [], 'admin'), Controller\FaqController::class, 'view', Faq::class, 'faq-view', 'fal fa-question', 'faq'],
            [$this->translator->trans('Carte', [], 'admin'), Controller\MapController::class, 'view', Map::class, 'map-view', 'fal fa-map-marked', 'map'],
            [$this->translator->trans('Informations de contact', [], 'admin'), Controller\InformationController::class, 'view', NULL, 'information-view', 'fal fa-info', 'information', true],
            [$this->translator->trans("Groupe d'onglets", [], 'admin'), Controller\TabController::class, 'view', Tab::class, 'tab-view', 'fal fa-layer-group', 'tab', true],
            [$this->translator->trans("Moteur de recherche", [], 'admin'), Controller\SearchController::class, 'view', Search::class, 'search-view', 'fal fa-search', 'search', true],
            [$this->translator->trans("Moteur de recherche & résultats", [], 'admin'), Controller\SearchController::class, 'results', Search::class, 'search-result-view', 'fal fa-search', 'search', true],
            [$this->translator->trans("Informations de contact", [], 'admin'), Controller\ContactController::class, 'view', Contact::class, 'contact-information-view', 'fal fa-file-signature', 'contact-information', true],
            [$this->translator->trans("Agenda", [], 'admin'), Controller\AgendaController::class, 'view', Agenda::class, 'agenda-view', 'fal fa-calendar-alt', 'agenda', true],
            [$this->translator->trans("Timeline", [], 'admin'), Controller\TimelineController::class, 'view', Timeline::class, 'timeline-view', 'fal fa-clock', 'timeline', true],
            [$this->translator->trans("Forum", [], 'admin'), Controller\ForumController::class, 'view', Forum::class, 'forum-view', 'fal fa-waveform', 'forum', true],
            [$this->translator->trans("Teaser d'évènements", [], 'admin'), Controller\EventController::class, 'teaser', EventEntities\Teaser::class, 'event-teaser', 'fal fa-calendar-week', 'event'],
            [$this->translator->trans("Liste d'évènements", [], 'admin'), Controller\EventController::class, 'index', EventEntities\Listing::class, 'event-index', 'fal fa-calendar-week', 'event'],
            [$this->translator->trans("Liste des produits", [], 'admin'), Controller\CatalogController::class, 'index', CatalogEntities\Listing::class, 'catalog-index', 'fal fa-book-open', 'catalog', true],
            [$this->translator->trans("Teaser de produits", [], 'admin'), Controller\CatalogController::class, 'teaser', CatalogEntities\Teaser::class, 'catalog-teaser', 'fal fa-book-open', 'catalog', true],
            [$this->translator->trans("Teaser de catégories de produits", [], 'admin'), Controller\CatalogController::class, 'teaserCategories', NULL, 'catalog-teaser-categories', 'fal fa-book-open', 'catalog', true],
            [$this->translator->trans("Panier de produits", [], 'admin'), Controller\CatalogController::class, 'cart', NULL, 'catalog-cart', 'fal fa-cart-arrow-down', 'catalog', true],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getDependencies(): array
    {
        return [
            ModuleFixture::class,
            SecurityFixture::class
        ];
    }
}