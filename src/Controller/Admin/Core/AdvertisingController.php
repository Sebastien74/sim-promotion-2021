<?php

namespace App\Controller\Admin\Core;

use App\Controller\Admin\AdminController;
use App\Entity\Core\Module;
use App\Entity\Layout\BlockType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * AdvertisingController
 *
 * Advertising management
 *
 * @Route("/admin-%security_token%/{website}/support", schemes={"%protocol%"})
 * @IsGranted("ROLE_ADMIN")
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class AdvertisingController extends AdminController
{
    /**
     * Display extensions view
     *
     * @Route("/extensions", methods={"GET", "POST"}, name="admin_advertising")
     * @param Request $request
     * @return string|Response
     */
    public function extensions(Request $request)
    {
        $extensions = [];
        $configuration = $this->getWebsite($request)->getConfiguration();

        $activesBlocksTypes = [];
        $blocksTypes = $this->entityManager->getRepository(BlockType::class)->findBy([], ['position' => 'ASC']);
        foreach ($configuration->getBlockTypes() as $blockType) {
            $activesBlocksTypes[] = $blockType->getSlug();
        }

        foreach ($blocksTypes as $key => $blockType) {
            $extensions[$key]['active'] = in_array($blockType->getSlug(), $activesBlocksTypes);
            $extensions[$key]['entity'] = $blockType;
        }

        $activesModules = [];
        $modules = $this->entityManager->getRepository(Module::class)->findBy([], ['position' => 'ASC']);
        foreach ($configuration->getModules() as $module) {
            $activesModules[] = $module->getSlug();
        }
        foreach ($modules as $key => $module) {
            $extensions[$key]['active'] = in_array($module->getSlug(), $activesModules);
            $extensions[$key]['entity'] = $module;
        }

        return $this->cache('admin/page/core/extensions.html.twig', [
            'extensions' => $extensions
        ]);
    }
}