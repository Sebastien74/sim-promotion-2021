<?php

namespace App\Controller\Admin\Seo;

use App\Controller\Admin\AdminController;
use App\Entity\Core\Domain;
use App\Entity\Core\Website;
use App\Entity\Seo\NotFoundUrl;
use App\Entity\Seo\Redirection;
use App\Form\Type\Seo\RedirectionType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * NotFoundUrlController
 *
 * SEO NotFoundUrl management
 *
 * @Route("/admin-%security_token%/{website}/seo/not-found-urls", schemes={"%protocol%"})
 * @IsGranted("ROLE_INTERNAL")
 *
 * @property NotFoundUrl $class
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class NotFoundUrlController extends AdminController
{
    protected $class = NotFoundUrl::class;

    /**
     * Index NotFoundUrl
     *
     * @Route("/{type}/{category}", methods={"GET", "POST"}, name="admin_notfoundurl_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        $all = [];
        $website = $this->getWebsite($request);
        $repository = $this->entityManager->getRepository(NotFoundUrl::class);
        $configuration = [
            'front' => ['url', 'resource'],
            'admin' => ['url', 'resource']
        ];

        foreach ($configuration as $type => $categories) {
            foreach ($categories as $category) {
                $all[$type][$category] = $repository->findByCategoryTypeQuery($website, $category, $type)->getResult();
            }
        }

        /** Unset URL if domain not configured for current website */
        $domainsDB = $this->entityManager->getRepository(Domain::class)->findBy(['configuration' => $website->getConfiguration()]);
        $domains = [];
        foreach ($domainsDB as $domainDB) {
            $domains[] = $domainDB->getName();
        }
        foreach ($all as $type => $categories) {
            foreach ($categories as $category => $urls) {
                foreach ($urls as $key => $url) {
                    $unset = true;
                    foreach ($domains as $domain) {
                        if(preg_match('/' . $domain . '/', $url->getUrl())) {
                            $unset = false;
                            break;
                        }
                    }
                    if($unset) {
                        unset($all[$type][$category][$key]);
                    }
                }
            }
        }

        $this->arguments['all'] = $all;
        $this->arguments['type'] = $request->get('type');
        $this->arguments['category'] = $request->get('category');
        $this->template = 'admin/page/seo/notfound-url.html.twig';

        $this->forceEntities = true;
        if(isset($all[$this->arguments['type']][$this->arguments['category']])) {
            $this->entities = $all[$this->arguments['type']][$this->arguments['category']];
        }
        else {
            throw $this->createNotFoundException();
        }

        return parent::index($request);
    }

    /**
     * Set new Redirection
     *
     * @Route("/redirection/new/{notfoundUrl}", methods={"GET", "POST"}, name="admin_notfoundurl_redirection")
     *
     * @param Request $request
     * @param NotFoundUrl $notfoundUrl
     * @return Response
     */
    public function newRedirection(Request $request, NotFoundUrl $notfoundUrl)
    {
        $notfoundUrl->setHaveRedirection(true);
        $this->entityManager->persist($notfoundUrl);

        $this->disableProfiler();
        $this->class = Redirection::class;
        $this->formType = RedirectionType::class;
        $this->formOptions['not_found_url'] = $notfoundUrl;
        $this->arguments['notfoundUrl'] = $notfoundUrl;
        $this->template = 'admin/page/seo/notfound-url-redirection.html.twig';

        return parent::new($request);
    }

    /**
     * Delete NotFoundUrl
     *
     * @Route("/delete/{notfoundurl}", methods={"DELETE"}, name="admin_notfoundurl_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }

    /**
     * Delete NotFoundUrl
     *
     * @Route("/delete/all/{type}/{category}", methods={"DELETE"}, name="admin_notfoundurl_delete_all")
     *
     * @IsGranted("ROLE_DELETE")
     * @param Request $request
     * @param Website $website
     * @param string $type
     * @param string $category
     * @return JsonResponse
     */
    public function deleteAll(Request $request, Website $website, string $type, string $category)
    {
        $notFounds = $this->entityManager->getRepository(NotFoundUrl::class)->findByCategoryTypeQuery(
            $website,
            $category,
            $type
        )->getResult();

        foreach ($notFounds as $notFound) {
            $this->entityManager->remove($notFound);
        }

        $this->entityManager->flush();

        return new JsonResponse(['success' => true, 'reload' => true]);
    }
}