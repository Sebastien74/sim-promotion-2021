<?php

namespace App\Controller\Admin\Core;

use App\Controller\Admin\AdminController;
use App\Entity\Core\Domain;
use App\Entity\Core\Website;
use App\Entity\Seo\NotFoundUrl;
use App\Entity\Seo\Url;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * DashboardController
 *
 * Dashboard management
 *
 * @Route("/admin-%security_token%", schemes={"%protocol%"})
 * @IsGranted("ROLE_ADMIN")
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class DashboardController extends AdminController
{
    /**
     * Dashboard view
     *
     * @Route("/dashboard/{website}", methods={"GET"}, name="admin_dashboard")
     *
     * @param Website $website
     * @return Response
     * @throws Exception
     */
    public function view(Website $website): Response
    {
        $noSeoCounts = $this->entityManager->getRepository(Url::class)->countEmptyLocalesSEO($website);

        return $this->cache('admin/page/core/dashboard.html.twig', [
            'notFoundUrls' => $this->getNotFoundUrls($website),
            'noSeoCounts' => $noSeoCounts
        ]);
    }

    /**
     * Get NotFoundUrl[]
     *
     * @param Website $website
     * @return array
     */
    private function getNotFoundUrls(Website $website): array
    {
        $notFoundUrls = $this->entityManager->getRepository(NotFoundUrl::class)->findBy([
            'type' => 'front',
            'category' => 'url',
            'haveRedirection' => false,
            'website' => $website
        ]);

        $domainsDB = $this->entityManager->getRepository(Domain::class)->findBy(['configuration' => $website->getConfiguration()]);

        $domains = [];
        foreach ($domainsDB as $domainDB) {
            $domains[] = $domainDB->getName();
        }

        /** Unset URL if domain not configured for current website */
        foreach ($notFoundUrls as $key => $url) {
            $unset = true;
            foreach ($domains as $domain) {
                if(preg_match('/' . $domain . '/', $url->getUrl())) {
                    $unset = false;
                    break;
                }
            }
            if($unset) {
                unset($notFoundUrls[$key]);
            }
        }

        return $notFoundUrls;
    }
}