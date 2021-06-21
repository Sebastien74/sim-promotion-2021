<?php

namespace App\Controller\Admin\Core;

use App\Controller\Admin\AdminController;
use App\Entity\Core\Configuration;
use App\Form\Manager\Core\ConfigurationManager;
use App\Form\Type\Core\Configuration\ConfigurationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * ConfigurationController
 *
 * Configuration management
 *
 * @Route("/admin-%security_token%/{website}/website/configuration", schemes={"%protocol%"})
 * @IsGranted("ROLE_INTERNAL")
 *
 * @property Configuration $class
 * @property ConfigurationType $formType
 * @property ConfigurationManager $formManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ConfigurationController extends AdminController
{
    protected $class = Configuration::class;
    protected $formType = ConfigurationType::class;
    protected $formManager = ConfigurationManager::class;

    /**
     * Edit Configuration style
     *
     * @Route("/edit", methods={"GET", "POST"}, name="admin_website_style")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        $configuration = $this->entityManager->getRepository(Configuration::class)->findOptimizedAdmin($this->getWebsite($request));
        if (!$configuration) {
            throw $this->createNotFoundException($this->translator->trans("Cette configuration n'existe pas !!", [], 'front'));
        }

        $this->subscriber->get($this->formManager)->synchronizeLocales($configuration);
        $this->template = 'admin/page/website/configuration.html.twig';
        $this->entity = $configuration;

        $this->disableProfiler();

        return parent::edit($request);
    }
}