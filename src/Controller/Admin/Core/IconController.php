<?php

namespace App\Controller\Admin\Core;

use App\Controller\Admin\AdminController;
use App\Entity\Core\Icon;
use App\Entity\Core\Website;
use App\Form\Manager\Core\IconManager;
use App\Form\Type\Core\IconType;
use App\Repository\Core\IconRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * IconController
 *
 * Icon management
 *
 * @Route("/admin-%security_token%/{website}/development/icons", schemes={"%protocol%"})
 * @IsGranted("ROLE_INTERNAL")
 *
 * @property Icon $class
 * @property IconType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class IconController extends AdminController
{
    /**
     * Icon library
     *
     * @Route("/library/{library}", methods={"GET", "POST"}, name="admin_icons")
     *
     * @param IconRepository $iconRepository
     * @param Website $website
     * @param string $library
     * @return Response
     */
    public function library(IconRepository $iconRepository, Website $website, string $library)
    {
        $icons = $iconRepository->findBy(['configuration' => $website->getConfiguration()]);
        $websiteIcons = [];
        foreach ($icons as $icon) {
            $websiteIcons[] = $icon->getPath();
        }

        return $this->cache('admin/page/core/icons.html.twig', [
            "websiteIcons" => $websiteIcons,
            "library" => $library,
            "libraries" => $this->getLibraries($icons),
        ]);
    }

    /**
     * Add Icon[]
     *
     * @Route("/icons-add", methods={"GET", "POST"}, name="admin_icons_add")
     *
     * @param Request $request
     * @param Website $website
     * @return Response
     */
    public function iconsAdd(Request $request, Website $website)
    {
        $form = $this->createForm(IconType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            return $this->subscriber->get(IconManager::class)->execute($website, $form);
        }

        return $this->cache('admin/page/core/icons-add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Add Icon
     *
     * @Route("/add", methods={"GET"}, name="admin_icon_add", options={"expose"=true})
     *
     * @param Request $request
     * @param IconRepository $iconRepository
     * @param Website $website
     * @return JsonResponse
     */
    public function add(Request $request, IconRepository $iconRepository, Website $website)
    {
        $path = json_decode(urldecode($request->get('path')));

        $existing = $iconRepository->findBy([
            'configuration' => $website->getConfiguration(),
            'path' => $path
        ]);

        if (!$existing) {
            $matches = explode('/', $path);
            $filename = end($matches);
            $manager = $this->subscriber->get(IconManager::class);
            $manager->addIcon($filename, $path, $website->getConfiguration());
        }

        return new JsonResponse(['success' => true]);
    }

    /**
     * Remove Icon
     *
     * @Route("/remove", methods={"GET"}, name="admin_icon_remove", options={"expose"=true})
     *
     * @param Request $request
     * @param Website $website
     * @return JsonResponse
     */
    public function remove(Request $request, Website $website)
    {
        $path = json_decode(urldecode($request->get('path')));
        $this->subscriber->get(IconManager::class)->remove($path, $website->getConfiguration());
        return new JsonResponse(['success' => true]);
    }

    /**
     * Get Libraries
     *
     * @param array $icons
     * @return array
     */
    private function getLibraries(array $icons)
    {
        $libraries = [];
        $dirname = $this->kernel->getProjectDir() . '/public/medias/icons';
        $dirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $dirname);
        $finder = new Finder();
        $finder->in($dirname);

        foreach ($finder as $file) {

            $projectDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $this->kernel->getProjectDir());
            $realPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $file->getRealPath());
            $realRelativePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $file->getRelativePath());
            $dirname = str_replace($projectDirname . DIRECTORY_SEPARATOR . 'public', '', $realPath);
            $params = ['src' => str_replace( DIRECTORY_SEPARATOR, '/', $dirname), 'filename' => $file->getFilename()];

            if ($file->getExtension() && preg_match('/\\' . DIRECTORY_SEPARATOR .'/', $realRelativePath)) {
                $matches = explode(DIRECTORY_SEPARATOR, $realRelativePath);
                $libraries[$matches[0]][] = $params;
            } elseif ($file->getExtension()) {
                if ($file->getRelativePath() === 'flags') {
                    $matchesLocale = explode('.', $file->getFilename());
                    $params['locale'] = $matchesLocale[0];
                }
                $libraries[$file->getRelativePath()][] = $params;
            }
        }

        foreach ($icons as $icon) {
            /** @var Icon $icon */
            $libraries['website'][] = ['src' => $icon->getPath(), 'filename' => $icon->getFilename(), 'locale' => $icon->getLocale()];
        }

        return $libraries;
    }
}