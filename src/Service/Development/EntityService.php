<?php

namespace App\Service\Development;

use App\Entity\Core\Entity;
use App\Entity\Core\Website;
use App\Entity\Security\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * EntityService
 *
 * Manage Entity configuration
 *
 * @property EntityManagerInterface $entityManager
 * @property KernelInterface $kernel
 * @property Website $website
 * @property User $createdBy
 * @property int $position
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class EntityService
{
    private $entityManager;
    private $kernel;
    private $website;
    private $createdBy;
    private $position;

    /**
     * EntityService constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param KernelInterface $kernel
     */
    public function __construct(EntityManagerInterface $entityManager, KernelInterface $kernel)
    {
        $this->entityManager = $entityManager;
        $this->kernel = $kernel;
    }

    /**
     * Execute
     *
     * @param Website $website
     * @param string $locale
     */
    public function execute(Website $website, string $locale)
    {
        $this->position = count($this->entityManager->getRepository(Entity::class)->findAll()) + 1;
        $metasData = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $configs = $this->getCoreValues();

        foreach ($metasData as $metadata) {
            if (!preg_match('/\\Base/', $metadata->getName())) {
                $this->setEntity($website, $metadata, $locale, $configs);
            }
        }

        $this->entityManager->flush();
    }

    /**
     * Get core values
     *
     * @return array
     */
    private function getCoreValues(): array
    {
        $values = [];
        $coreDirname = $this->kernel->getProjectDir() . '/bin/data/fixtures/';
        $coreDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $coreDirname);
        $imports = Yaml::parseFile($coreDirname . 'entity-configuration.yaml')['imports'];
        foreach ($imports as $import) {
            $values = array_merge($values, Yaml::parseFile($coreDirname . $import['resource']));
        }

        return $values;
    }

    /**
     * Set Entity
     *
     * @param Website $website
     * @param ClassMetadata $metadata
     * @param string $locale
     * @param array $configs
     */
    private function setEntity(Website $website, ClassMetadata $metadata, string $locale, array $configs)
    {
        $classname = $metadata->getName();
        $config = !empty($configs[$classname]) ? $configs[$classname] : [];

        /** @var Entity $existing */
        $existing = $this->entityManager->getRepository(Entity::class)->findOneBy([
            'website' => $website,
            'className' => $metadata->getName()
        ]);

        $entity = $existing ? $existing : new Entity();
        $entity->setAdminName($this->getAdminName($metadata, $config, $locale));

        if (!$existing) {

            $entity->setWebsite($website);
            $entity->setClassName($metadata->getName());
            $entity->setColumns($this->getColumns($entity, $config));
            $entity->setSearchFields($this->getSearchFields($entity, $config));
            $entity->setSearchFilters($this->getSearchFilters($entity, $config));
            $entity->setOrderBy($this->getOrderBy($entity, $config));
            $entity->setOrderSort($this->getOrderSort($entity, $config));
            $entity->setShowView($this->getShowView($entity, $config));
            $entity->setExports($this->getExports($entity, $config));
            $entity->setUniqueLocale($this->getUniqueLocale($entity, $config));
            $entity->setMediaMulti($this->getMediaMulti($entity, $config));
            $entity->setAsCard($this->getAsCard($entity, $config));
            $entity->setPosition($this->position);
            $entity->setAdminLimit($this->getAdminLimit($entity, $config));

            $session = new Session();
            $sessionSlug = str_replace('\\', '_', $entity->getClassName());
            $session->remove('configuration_' . $sessionSlug);

            if ($this->createdBy) {
                $entity->setCreatedBy($this->createdBy);
            }
        }

        $this->entityManager->persist($entity);

        $this->position++;
    }

    /**
     * Get AdminName
     *
     * @param ClassMetadata $metadata
     * @param array $config
     * @param string $locale
     * @return string
     */
    public function getAdminName(ClassMetadata $metadata, array $config, string $locale)
    {
        $adminName = !empty($config['translations']['singular'][$locale]) ? $config['translations']['singular'][$locale] : $metadata->getName();
        return ltrim($adminName, '__');
    }

    /**
     * Get columns
     *
     * @param Entity $entity
     * @param array $config
     * @return array
     */
    public function getColumns(Entity $entity, array $config)
    {
        $columns = !empty($config['columns']) ? $config['columns'] : [];

        if (!$columns) {
            $columns = $entity->getColumns();
        }

        return $columns;
    }

    /**
     * Get search fields
     *
     * @param Entity $entity
     * @param array $config
     * @return array
     */
    public function getSearchFields(Entity $entity, array $config)
    {
        $columns = !empty($config['searchFields']) ? $config['searchFields'] : (!empty($config['columns']) ? $config['columns'] : []);

        if (!$columns) {
            $columns = $entity->getSearchFields();
        }

        return $columns;
    }

    /**
     * Get search filter fields
     *
     * @param Entity $entity
     * @param array $config
     * @return array
     */
    public function getSearchFilters(Entity $entity, array $config)
    {
        $columns = !empty($config['searchFilters']) ? $config['searchFilters'] : [];

        if (!$columns) {
            $columns = $entity->getSearchFilters();
        }

        return $columns;
    }

    /**
     * Get orderBy
     *
     * @param Entity $entity
     * @param array $config
     * @return string
     */
    public function getOrderBy(Entity $entity, array $config)
    {
        return !empty($config['orderBy']) ? $config['orderBy'] : $entity->getOrderBy();
    }

    /**
     * Get orderSort
     *
     * @param Entity $entity
     * @param array $config
     * @return string
     */
    public function getOrderSort(Entity $entity, array $config)
    {
        return !empty($config['orderSort']) ? $config['orderSort'] : $entity->getOrderSort();
    }

    /**
     * Get columns
     *
     * @param Entity $entity
     * @param array $config
     * @return array
     */
    public function getShowView(Entity $entity, array $config)
    {
        $show = !empty($config['show']) ? $config['show'] : [];

        if (empty($show)) {
            $show = $entity->getShowView();
        }

        return $show;
    }

    /**
     * Get exports
     *
     * @param Entity $entity
     * @param array $config
     * @return array
     */
    public function getExports(Entity $entity, array $config)
    {
        return !empty($config['exports']) ? $config['exports'] : $entity->getExports();
    }

    /**
     * Get unique Locale
     *
     * @param Entity $entity
     * @param array $config
     * @return bool
     */
    public function getUniqueLocale(Entity $entity, array $config)
    {
        return isset($config['uniqueLocale']) ? $config['uniqueLocale'] : $entity->getUniqueLocale();
    }

    /**
     * Get media multi
     *
     * @param Entity $entity
     * @param array $config
     * @return bool
     */
    public function getMediaMulti(Entity $entity, array $config)
    {
        return isset($config['mediaMulti']) ? $config['mediaMulti'] : $entity->getMediaMulti();
    }

    /**
     * Get is seo card
     *
     * @param Entity $entity
     * @param array $config
     * @return bool
     */
    public function getAsCard(Entity $entity, array $config)
    {
        return isset($config['asCard']) ? $config['asCard'] : $entity->getAsCard();
    }

    /**
     * Get admin limit
     *
     * @param Entity $entity
     * @param array $config
     * @return int
     */
    public function getAdminLimit(Entity $entity, array $config)
    {
        return isset($config['adminLimit']) ? $config['adminLimit'] : $entity->getAdminLimit();
    }

    /**
     * Set Website
     *
     * @param Website $website
     */
    public function setWebsite(Website $website)
    {
        $this->website = $website;
    }

    /**
     * Set CreatedBy
     *
     * @param User $createdBy
     */
    public function setCreatedBy(User $createdBy = NULL)
    {
        $this->createdBy = $createdBy;
    }
}