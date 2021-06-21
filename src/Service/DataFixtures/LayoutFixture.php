<?php

namespace App\Service\DataFixtures;

use App\Entity\Module\Catalog as CatalogEntities;
use App\Entity\Module\Form\Form;
use App\Entity\Module\Event as EventEntities;
use App\Entity\Module\Making as MakingEntities;
use App\Entity\Module\Newscast as NewscastEntities;
use App\Entity\Module\Portfolio as PortfolioEntities;
use App\Entity\Core\Configuration;
use App\Entity\Core\Module;
use App\Entity\Layout\BlockType;
use App\Entity\Layout\LayoutConfiguration;
use App\Entity\Layout\Page;
use App\Entity\Security\User;
use Doctrine\ORM\EntityManagerInterface;

/**
 * LayoutFixture
 *
 * Layout Fixture management
 *
 * @property EntityManagerInterface $entityManager
 * @property BlockType[] $blockTypes
 * @property Module[] $modules
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class LayoutFixture
{
    private $entityManager;
    private $blockTypes;
    private $modules;

    /**
     * LayoutFixture constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->blockTypes = $this->entityManager->getRepository(BlockType::class)->findAll();
        $this->modules = $this->entityManager->getRepository(Module::class)->findAll();
    }

    /**
     * Add entity LayoutConfiguration
     *
     * @param Configuration $configuration
     * @param bool $devMode
     * @param array $defaultsModules
     * @param array $othersModules
     * @param User|null $user
     */
    public function add(Configuration $configuration, bool $devMode, array $defaultsModules, array $othersModules, User $user = NULL)
    {
        $configurationBlockTypes = $this->getBlockTypesIds($configuration);
        $layoutConfigurations = $this->getConfiguration($devMode, $defaultsModules, $othersModules);

        $position = 1;
        foreach ($layoutConfigurations as $classname => $config) {

            $layoutConfiguration = new LayoutConfiguration();
            $layoutConfiguration->setAdminName($config->adminName);
            $layoutConfiguration->setEntity($classname);
            $layoutConfiguration->setPosition($position);
            $layoutConfiguration->setWebsite($configuration->getWebsite());

            if ($user) {
                $layoutConfiguration->setCreatedBy($user);
            }

            $position++;

            foreach ($this->blockTypes as $blockType) {
                if (in_array($blockType->getCategory(), $config->blocks) && in_array($blockType->getId(), $configurationBlockTypes)) {
                    $layoutConfiguration->addBlockType($blockType);
                }
            }

            foreach ($this->modules as $module) {
                if (in_array($module->getRole(), $config->modules)) {
                    $layoutConfiguration->addModule($module);
                }
            }

            $this->entityManager->persist($layoutConfiguration);
        }
    }

    /**
     * Get configuration BlockType ids
     *
     * @param Configuration $configuration
     * @return array
     */
    private function getBlockTypesIds(Configuration $configuration): array
    {
        $configurationBlockTypes = [];

        foreach ($configuration->getBlockTypes() as $blockType) {
            $configurationBlockTypes[] = $blockType->getId();
        }

        return $configurationBlockTypes;
    }

    /**
     * Get configuration
     *
     * @param bool $devMode
     * @param array $defaultsModules
     * @param array $othersModules
     * @return array
     */
    private function getConfiguration(bool $devMode, array $defaultsModules, array $othersModules): array
    {
        $fullDefaultModules = $devMode ? array_merge($defaultsModules, $othersModules) : ['ROLE_NEWSCAST', 'ROLE_FORM', 'ROLE_SLIDER'];

        return [
            Page::class => (object)[
                'adminName' => "Page",
                'blocks' => ['content', 'global'],
                'modules' => array_merge(['ROLE_CONTACT', 'ROLE_FAQ', 'ROLE_SITE_MAP'], $fullDefaultModules)
            ],
            NewscastEntities\Category::class => (object)[
                'adminName' => "Catégorie d'actualité",
                'blocks' => ['layout'],
                'modules' => []
            ],
            NewscastEntities\Newscast::class => (object)[
                'adminName' => "Fiche actualité",
                'blocks' => ['content', 'global'],
                'modules' => $fullDefaultModules
            ],
            EventEntities\Category::class => (object)[
                'adminName' => "Catégorie d'évènement",
                'blocks' => ['layout'],
                'modules' => []
            ],
            EventEntities\Event::class => (object)[
                'adminName' => "Fiche évènement",
                'blocks' => ['content', 'global'],
                'modules' => $fullDefaultModules
            ],
            CatalogEntities\Catalog::class => (object)[
                'adminName' => "Catalogue",
                'blocks' => ['layout', 'layout-map', 'layout-catalog'],
                'modules' => []
            ],
            CatalogEntities\Product::class => (object)[
                'adminName' => "Fiche produit",
                'blocks' => ['content', 'global', 'layout-map', 'layout-catalog'],
                'modules' => $fullDefaultModules
            ],
            Form::class => (object)[
                'adminName' => "Formulaire",
                'blocks' => ['form'],
                'modules' => []
            ],
            PortfolioEntities\Category::class => (object)[
                'adminName' => "Catégorie de portfolio",
                'blocks' => ['layout'],
                'modules' => []
            ],
            PortfolioEntities\Card::class => (object)[
                'adminName' => "Fiche portfolio",
                'blocks' => ['content', 'global'],
                'modules' => $fullDefaultModules
            ],
            MakingEntities\Category::class => (object)[
                'adminName' => "Catégorie de réalisation",
                'blocks' => ['content', 'global'],
                'modules' => $fullDefaultModules
            ],
            MakingEntities\Making::class => (object)[
                'adminName' => "Réalisation",
                'blocks' => ['content', 'global'],
                'modules' => $fullDefaultModules
            ],
        ];
    }
}