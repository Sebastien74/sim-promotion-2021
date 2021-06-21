<?php

namespace App\Service\DataFixtures;

use App\Entity\Module\Catalog\Product;
use App\Entity\Module\Faq\Question;
use App\Entity\Module\Newscast\Newscast;
use App\Entity\Module\Newscast\Teaser;
use App\Entity\Module\Slider\Slider;
use App\Entity\Core\Website;
use App\Entity\Layout\Block;
use App\Entity\Layout\BlockType;
use App\Entity\Layout\Page;
use App\Entity\Media\ThumbAction;
use App\Entity\Media\ThumbConfiguration;
use App\Entity\Security\User;
use Doctrine\ORM\EntityManagerInterface;

/**
 * ThumbnailFixture
 *
 * Thumbnail Fixture management
 *
 * @property EntityManagerInterface $entityManager
 * @property User $user
 * @property Website $website
 * @property int $position
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ThumbnailFixture
{
    private $entityManager;
    private $user;
    private $website;
    private $position = 1;

    /**
     * ThumbnailFixture constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Add ThumbnailFixture
     *
     * @param Website $website
     * @param User|NULL $user
     */
    public function add(Website $website, User $user = NULL)
    {
        $this->user = $user;
        $this->website = $website;

        $teaser = $this->entityManager->getRepository(Teaser::class)->findOneBy(['website' => $this->website]);
        $slider = $this->entityManager->getRepository(Slider::class)->findOneBy(['website' => $this->website]);
        $headerTitle = $this->entityManager->getRepository(BlockType::class)->findOneBy(['slug' => 'titleheader']);

        $this->addConfig("Thumbnail 400 x 300", 400, 300, "Liste des actualités", Newscast::class, 'index');
        $this->addConfig("Thumbnail 468 x Infinite", 468, NULL, "Actualité mise en avant index", Newscast::class, 'index', 'first-news-index');
        if($teaser instanceof Teaser) {
            $this->addConfig("Thumbnail 332 x 246", 332, 246, "Teaser d'actualités accueil", Newscast::class, 'teaser', $teaser->getId());
        }
        if($slider instanceof Slider) {
            $this->addConfig("Thumbnail 1600 x 504", 1600, 504, "Carousel accueil", Slider::class, 'view', $slider->getId());
        }
        $this->addConfig("Thumbnail 1600 x 214", 1600, 214, "Block entête", Block::class, 'block', $headerTitle);
        $this->addConfig("Thumbnail 315 x 230", 315, 230, "Teaser de produits", Product::class, 'teaser');
        $this->addConfig("Thumbnail 1600 x 500", 1600, 500, "Teaser de produits carrousel", Product::class, 'teaser');
        $this->addConfig("Infinite");
    }

    /**
     * Add configuration
     *
     * @param string $thumbConfigName
     * @param int|NULL $width
     * @param int|NULL $height
     * @param string|NULL $thumbActionName
     * @param string|NULL $classname
     * @param string|NULL $actionName
     * @param int|BlockType|NULL $filter
     */
    private function addConfig(
        string $thumbConfigName,
        int $width = NULL,
        int $height = NULL,
        string $thumbActionName = NULL,
        string $classname = NULL,
        string $actionName = NULL,
        $filter = NULL)
    {
        $configuration = new ThumbConfiguration();
        $configuration->setAdminName($thumbConfigName);
        $configuration->setWidth($width);
        $configuration->setHeight($height);
        $configuration->setConfiguration($this->website->getConfiguration());
        $configuration->setPosition($this->position);

        $this->position++;

        if ($thumbConfigName === 'Infinite') {
            $this->addThumbConfiguration($configuration, "Page", Page::class);
            $this->addThumbConfiguration($configuration, "FAQ", Question::class);
            $this->addThumbConfiguration($configuration, "Carrousel", Slider::class);
            $blockMedia = $this->entityManager->getRepository(BlockType::class)->findOneBy(['slug' => 'media']);
            $this->addThumbConfiguration($configuration, "Bloc média", Block::class, 'block', $blockMedia);
            $blockCard = $this->entityManager->getRepository(BlockType::class)->findOneBy(['slug' => 'card']);
            $this->addThumbConfiguration($configuration, "Bloc mini fiche", Block::class, 'block', $blockCard);
        } else {
            $this->addThumbConfiguration($configuration, $thumbActionName, $classname, $actionName, $filter);
        }

        if ($this->user) {
            $configuration->setCreatedBy($this->user);
        }

        $this->entityManager->persist($configuration);
    }

    /**
     * Add ThumbConfiguration
     *
     * @param ThumbConfiguration $configuration
     * @param string|NULL $thumbActionName
     * @param string|NULL $classname
     * @param string|NULL $actionName
     * @param int|BlockType|NULL $filter
     */
    private function addThumbConfiguration(
        ThumbConfiguration $configuration,
        string $thumbActionName = NULL,
        string $classname = NULL,
        string $actionName = NULL,
        $filter = NULL)
    {
        $action = new ThumbAction();
        $action->setAdminName($thumbActionName);
        $action->setNamespace($classname);
        $action->setAction($actionName);

        if ($classname === Block::class) {
            $action->setBlockType($filter);
        } else {
            $action->setActionFilter($filter);
        }

        $configuration->addAction($action);

        if ($this->user) {
            $action->setCreatedBy($this->user);
        }
    }
}