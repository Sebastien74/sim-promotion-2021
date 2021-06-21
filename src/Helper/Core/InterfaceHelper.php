<?php

namespace App\Helper\Core;

use App\Entity\Core\Entity;
use App\Entity\Core\Website;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * InterfaceHelper
 *
 * @property EntityManagerInterface $entityManager
 * @property Request $request
 * @property Website $website
 * @property array $interface
 * @property string $masterField
 * @property string $parentMasterField
 * @property array $labels
 * @property mixed $entity
 * @property string $name
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class InterfaceHelper
{
    private $entityManager;
    private $request;
    private $website;
    private $interface = [];
    private $masterField;
    private $parentMasterField;
    private $labels;
    private $entity;
    private $name;

    /**
     * InterfaceHelper constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param RequestStack $requestStack
     */
    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack)
    {
        $this->entityManager = $entityManager;
        $this->request = $requestStack->getMasterRequest();

        if ($this->request) {
            $session = new Session();
            $this->website = preg_match('/\/admin-' . $_ENV['SECURITY_TOKEN'] . '/', $this->request->getUri())
                ? $session->get('adminWebsite')
                : $session->get('frontWebsite');
            if(is_array($this->website) && !empty($this->website['id'])) {
                $this->website = $this->entityManager->getRepository(Website::class)->find($this->website['id']);
            }
        }
    }

    /**
     * Generate Interface
     *
     * @param string|null $classname
     * @return bool|array
     */
    public function generate(string $classname = NULL)
    {
        if(!$classname) {
            return false;
        }

        $this->setInterface($classname);
        return $this->getInterface();
    }

    /**
     * Get Interface
     */
    public function getInterface(): array
    {
        return $this->interface;
    }

    /**
     * Set Interface
     *
     * @param string $classname
     */
    public function setInterface(string $classname): void
    {
        $matchesEntity = explode('\\', $classname);
        $referClass = class_exists($classname) ? new $classname() : NULL;
        $this->interface = is_object($referClass) && method_exists($referClass, 'getInterface') && !empty($referClass::getInterface())
            ? $referClass::getInterface()
            : [];
        $actionCode = preg_match('/Module/', $classname) && !empty($matchesEntity[count($matchesEntity) - 2])
            ? strtolower($matchesEntity[count($matchesEntity) - 2])
            : NULL;

        $interfaceName = !empty($this->interface['name']) ? $this->interface['name'] : NULL;
        $this->interface['name'] = $interfaceName === 'website' ? 'site' : $interfaceName;

        $this->setEntity($classname);

        $website = $this->request && $this->request->get('website')
            ? $this->entityManager->getRepository(Website::class)->find($this->request->get('website'))
            : NULL;

        $this->setMasterField();
        $this->setParentMasterField();
        $this->setLabels();

        $this->interface['masterField'] = $this->masterField;
        $this->interface['masterFieldId'] = $this->request && $this->masterField ? $this->request->get($this->masterField) : NULL;
        $this->interface['parentMasterField'] = $this->parentMasterField;
        $this->interface['parentMasterFieldId'] = $this->request && $this->parentMasterField ? $this->request->get($this->parentMasterField) : NULL;
        $this->interface['website'] = $website;
        $this->interface['entityCode'] = strtolower(end($matchesEntity));
        $this->interface['actionCode'] = $actionCode;
        $this->interface['classname'] = $classname;
        $this->interface['configuration'] = $this->getConfiguration($classname);
        $this->interface['labels'] = $this->labels;
        $this->interface['entity'] = $this->getEntity();
    }

    /**
     * Get MasterField
     */
    public function getMasterField()
    {
        return $this->masterField;
    }

    /**
     * Set MasterField
     */
    public function setMasterField(): void
    {
        $this->masterField = method_exists($this->getEntity(), 'getMasterField') && !empty($this->getEntity()::getMasterField())
            ? $this->getEntity()::getMasterField()
            : NULL;
    }

    /**
     * Set MasterField
     */
    public function setParentMasterField(): void
    {
        $this->parentMasterField = method_exists($this->getEntity(), 'getParentMasterField') && !empty($this->getEntity()::getParentMasterField())
            ? $this->getEntity()::getParentMasterField()
            : NULL;
    }

    /**
     * Get Labels
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * Set Labels
     */
    public function setLabels(): void
    {
        $this->labels = method_exists($this->getEntity(), 'getLabels') && !empty($this->getEntity()::getLabels())
            ? $this->getEntity()::getLabels()
            : NULL;
    }

    /**
     * Get Entity
     */
    public function getEntity()
    {
        if (is_object($this->entity) && $this->entity instanceof Website) {
            $configuration = $this->entity->getConfiguration();
            if ($configuration) {
                $this->entityManager->refresh($configuration);
            }
        }

        return $this->entity;
    }

    /**
     * Set Entity
     *
     * @param string $classname
     */
    public function setEntity(string $classname): void
    {
        if ($this->request) {

            if (!empty($this->interface['name']) && !empty($this->request->get($this->interface['name'])) && $this->interface['name'] !== 'configuration') {
                if (is_numeric($this->request->get($this->interface['name']))) {
                    $this->entity = $this->entityManager->getRepository($classname)->find($this->request->get($this->interface['name']));
                }
            } elseif ($this->interface['name'] !== 'configuration' && $this->interface['name'] && !$this->request->get($this->interface['name'])) {
                $this->entity = class_exists($classname) ? new $classname() : NULL;
            }

            if (!$this->entity && $this->interface['name'] && is_array($this->request->get($this->interface['name']))) {
                $this->entity = class_exists($classname) ? new $classname() : NULL;
            }
        }
    }

    /**
     * Get Name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set Name
     *
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Get Entity Configuration
     *
     * @param string $classname
     * @return object
     */
    public function getConfiguration(string $classname)
    {
        $session = new Session();
        $sessionSlug = str_replace('\\', '_', $classname);
        $configuration = $session->get('configuration_' . $sessionSlug);

        if (!$configuration) {

            $entity = $this->entityManager->getRepository(Entity::class)->optimizedQuery($classname, $this->website);
            $properties = $this->entityManager->getClassMetadata(Entity::class)->getReflectionProperties();
            $default = new Entity();

            $configuration = [];
            foreach ($properties as $property => $reflexionProperty) {
                $method = 'get' . ucfirst($property);
                $configuration[$property] = !$entity ? $default->$method() : $entity->$method();
            }

            $session->set('configuration_' . $sessionSlug, $configuration);
        }

        return (object)$configuration;
    }
}