<?php

namespace App\Controller\Admin\Seo;

use App\Controller\Admin\AdminController;
use App\Entity\Seo\SeoConfiguration;
use App\Form\Manager\Api\FacebookManager;
use App\Form\Manager\Api\GoogleManager;
use App\Form\Manager\Information\SocialNetworkManager;
use App\Form\Type\Seo\Configuration\ConfigurationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * ConfigurationController
 *
 * SEO configuration management
 *
 * @Route("/admin-%security_token%/{website}/seo/configuration", schemes={"%protocol%"})
 * @IsGranted("ROLE_INTERNAL")
 *
 * @property SeoConfiguration $class
 * @property ConfigurationType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ConfigurationController extends AdminController
{
    protected $class = SeoConfiguration::class;
    protected $formType = ConfigurationType::class;

    /**
     * Edit SeoConfiguration
     *
     * @Route("/edit", methods={"GET", "POST"}, name="admin_seoconfiguration_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        $website = $this->getWebsite($request);

        $this->entity = $this->entityManager->getRepository(SeoConfiguration::class)->findOneBy(['website' => $website]);
        if (!$this->entity) {
            throw $this->createNotFoundException($this->translator->trans("Cette configuration n'existe pas !!", [], 'admin'));
        }

        $this->template = 'admin/page/seo/configuration.html.twig';
        $this->subscriber->get(SocialNetworkManager::class)->synchronizeLocales($website, $this->entity);
        $this->subscriber->get(GoogleManager::class)->synchronizeLocales($website, $this->entity);
        $this->subscriber->get(FacebookManager::class)->synchronizeLocales($website, $this->entity);

        return parent::edit($request);
    }
}