<?php

namespace App\Controller\Admin\Seo;

use App\Entity\Core\Website;
use App\Entity\Layout\Page;
use App\Entity\Seo\Seo;
use App\Entity\Seo\Session;
use App\Entity\Seo\Url;
use App\Form\Type\Seo\SeoType;
use App\Service\Content\SeoService;
use Doctrine\ORM\NonUniqueResultException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * SeoController
 *
 * SEO management
 *
 * @Route("/admin-%security_token%/{website}/seo", schemes={"%protocol%"})
 * @IsGranted("ROLE_SEO")
 *
 * @property Seo $class
 * @property SeoType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SeoController extends BaseController
{
    protected $class = Seo::class;
    protected $formType = SeoType::class;

	/**
	 * Edit Seo
	 *
	 * @Route("/edit/{entitylocale}/{url}", methods={"GET", "POST"}, defaults={"url"=NULL}, name="admin_seo_edit")
	 *
	 * {@inheritdoc}
	 * @throws NonUniqueResultException
	 */
    public function edit(Request $request)
    {
        $website = $this->getWebsite($request);

        $this->template = 'admin/page/seo/edit.html.twig';

        $this->getEntity($request, $website);
        $this->getEntities($request, $website);

        if (isset($this->arguments['seo']['haveIndexPage']) && $this->arguments['seo']['haveIndexPage']) {
            $this->formOptions['have_index_page'] = $this->arguments['seo']['haveIndexPage'];
        }

        return parent::edit($request);
    }

	/**
	 * Get entity
	 *
	 * @param Request $request
	 * @param Website $website
	 * @throws NonUniqueResultException
	 */
    private function getEntity(Request $request, Website $website)
    {
        $url = NULL;

        if (!$request->get('url')) {

            $page = $this->entityManager->getRepository(Page::class)->findOneBy([
                'isIndex' => true,
                'website' => $website
            ]);

            if (!$page) {
                throw $this->createNotFoundException($this->translator->trans("Vous devez définir une page d'accueil !!", [], 'admin'));
            }

            $url = $this->getUrl($request, $page);

        } elseif ($request->get('url')) {
            $url = $this->entityManager->getRepository(Url::class)->find($request->get('url'));
        }

        if ($url instanceof Url && $url->getLocale() !== $request->get('entitylocale')) {
            throw $this->createNotFoundException();
        }

        $this->arguments['currentUrl'] = $url;

        if ($url) {
            $this->entity = $this->setSeo($url, $website);
            $this->arguments['seo'] = $this->subscriber->get(SeoService::class)->execute($url, $this->entity);
        }
    }

    /**
     * Get Url
     *
     * @param Request $request
     * @param mixed $entity
     * @return Url
     */
    private function getUrl(Request $request, $entity): ?Url
	{
        foreach ($entity->getUrls() as $url) {
            /** @var Url $url */
            if ($url->getLocale() === $request->get('entitylocale')) {
                return $url;
            }
        }

        return NULL;
    }

    /**
     * Set Seo
     *
     * @param Url $url
     * @param Website $website
     * @return Seo|null
     */
    private function setSeo(Url $url, Website $website): ?Seo
	{
        if (!$url->getWebsite()) {
            $url->setWebsite($website);
        }

        if ($url->getSeo()) {
            return $url->getSeo();
        }

        $seo = new Seo();
        $seo->setUrl($url);
        $url->setSeo($seo);

        $this->entityManager->persist($seo);
        $this->entityManager->flush();

        return $seo;
    }

	/**
	 * Set Seo
	 *
	 * @param Url $url
	 * @return string|null
	 * @throws NonUniqueResultException
	 */
    private function getParentEntityClassname(Url $url): ?string
	{
		$metasData = $this->entityManager->getMetadataFactory()->getAllMetadata();

		foreach ($metasData as $metaData) {

			$classname = $metaData->getName();
			$baseEntity = $metaData->getReflectionClass()->getModifiers() === 0 ? new $classname() : NULL;

			if ($classname !== Session::class && $baseEntity && method_exists($baseEntity, 'getUrls') && method_exists($baseEntity, 'getWebsite')) {

				$entity = $this->entityManager->getRepository($classname)
					->createQueryBuilder('e')
					->leftJoin('e.website', 'w')
					->leftJoin('e.urls', 'u')
					->andWhere('u.id = :id')
					->setParameter('id', $url->getId())
					->addSelect('w')
					->addSelect('u')
					->getQuery()
					->getOneOrNullResult();

				if($entity) {
					return $classname;
				}
			}
		}

		return NULL;
    }
}