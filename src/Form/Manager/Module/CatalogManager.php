<?php

namespace App\Form\Manager\Module;

use App\Entity\Core\Module;
use App\Entity\Core\Website;
use App\Entity\Module\Catalog\Catalog;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * CatalogManager
 *
 * Manage Product in admin
 *
 * @property EntityManagerInterface $entityManager
 * @property Request $request
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CatalogManager
{
    private $entityManager;
    private $request;

    /**
     * CatalogManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param RequestStack $requestStack
     */
    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack)
    {
        $this->entityManager = $entityManager;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @prePersist
     *
     * @param Catalog $catalog
     * @param Website $website
     * @param array $interface
     * @param Form $form
     */
    public function prePersist(Catalog $catalog, Website $website, array $interface, Form $form)
    {
        $this->setOptions($catalog, $website);

        $this->entityManager->persist($catalog);
    }

    /**
     * To set options
     *
     * @param Catalog $catalog
     * @param Website $website
     */
    private function setOptions(Catalog $catalog, Website $website)
    {
        $associateModules = [
            'real-estate-programs' => ['information' => true, 'lots' => true],
        ];

        foreach ($associateModules as $code => $options) {
            foreach ($website->getConfiguration()->getModules() as $module) {
                if ($module->getSlug() === $code) {
                    foreach ($options as $property => $value) {
                        $setter = 'set' . ucfirst($property);
                        $catalog->$setter($value);
                    }
                }
            }
        }
    }
}