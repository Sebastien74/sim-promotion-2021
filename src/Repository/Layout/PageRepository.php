<?php

namespace App\Repository\Layout;

use App\Entity\Core\Website;
use App\Entity\Layout\Block;
use App\Entity\Layout\Page;
use App\Entity\Seo\Url;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * PageRepository
 *
 * @method Page|null find($id, $lockMode = null, $lockVersion = null)
 * @method Page|null findOneBy(array $criteria, array $orderBy = null)
 * @method Page[]    findAll()
 * @method Page[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class PageRepository extends ServiceEntityRepository
{
    /**
     * PageRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Page::class);
    }

    /**
     * Find Page as array
     *
     * @param int $id
     * @return array
     */
    public function findArray(int $id): array
    {
        $result = $this->createQueryBuilder('p')
            ->leftJoin('p.layout', 'l')
            ->andWhere('p.id = :id')
            ->setParameter('id', $id)
            ->addSelect('l')
            ->getQuery()
            ->getArrayResult();

        return !empty($result[0]) ? $result[0] : [];
    }

    /**
     * Find Index
     *
     * @param mixed $website
     * @param string $locale
     * @param bool $preview
     * @param bool $hasArray
     * @return Page|array|null
     * @throws NonUniqueResultException
     */
    public function findIndex($website, string $locale, bool $preview = false, bool $hasArray = false)
    {
        $queryBuilder = $this->optimizedQueryBuilder($website, $locale, $preview, $hasArray)
            ->andWhere('p.isIndex = :isIndex')
            ->setParameter('isIndex', true)
            ->getQuery();

        if ($hasArray) {
            $result = $queryBuilder->getArrayResult();
            return !empty($result[0]) ? $result[0] : [];
        } else {
            return $queryBuilder->getOneOrNullResult();
        }
    }

    /**
     * Find for Tree position
     *
     * @param Website $website
     * @param Page $page
     * @return Page[]
     */
    public function findForTreePosition(Website $website, Page $page): array
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->leftJoin('p.urls', 'u')
            ->andWhere('u.isArchived = :isArchived')
            ->andWhere('p.deletable = :deletable')
            ->andWhere('p.website = :website')
            ->setParameter('isArchived', false)
            ->setParameter('deletable', true)
            ->setParameter('website', $website)
            ->addSelect('u');

        if (!$page->getParent()) {
            $queryBuilder->andWhere('p.parent IS NULL');
        } else {
            $queryBuilder->andWhere('p.parent = :parent')
                ->setParameter('parent', $page->getParent());
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Find by URL code and locale
     *
     * @param mixed $website
     * @param string $urlCode
     * @param string $locale
     * @param bool $preview
     * @param bool $hasArray
     * @return Page|array|null
     * @throws NonUniqueResultException
     */
    public function findByUrlCodeAndLocale($website, string $urlCode, string $locale, bool $preview, bool $hasArray = false)
    {
        $pageQb = $this->optimizedQueryBuilder($website, $locale, $preview, $hasArray)
            ->andWhere('u.code = :code')
            ->setParameter('code', $urlCode)
            ->getQuery();

        if ($hasArray) {
            $result = $pageQb->getArrayResult();
            $page = !empty($result[0]) ? $result[0] : [];
        } else {
            $page = $pageQb->getOneOrNullResult();
        }

        if (!$page) {
            $pageQb = $this->optimizedQueryBuilder($website, $locale, true)
                ->andWhere('u.code = :code')
                ->setParameter('code', $urlCode)
                ->getQuery();
            if ($hasArray) {
                $result = $pageQb->getArrayResult();
                $page = !empty($result[0]) ? $result[0] : [];
            } else {
                $page = $pageQb->getOneOrNullResult();
            }
        }

        if ($page instanceof Page && $page->getInfill() && $page->getPages()->count() > 0
            || $hasArray && !empty($page['pages'])) {
            $pages = $hasArray ? $page['pages'] : $page->getPages();
            foreach ($pages as $page) {
                $urls = $hasArray ? $page['urls'] : $page->getUrls();
                foreach ($urls as $url) {
                    $localeUrl = $hasArray ? $url['locale'] : $url->getLocale();
                    $isOnline = $hasArray ? $url['isOnline'] : $url->getIsOnline();
                    if ($localeUrl === $locale && $isOnline) {
                        $code = $hasArray ? $url['code'] : $url->getCode();
                        return ['redirection' => $code];
                    }
                }
            }
        }

        return $page;
    }

	/**
	 * Find by URL code and locale
	 *
	 * @param array $website
	 * @param string $locale
	 * @return array
	 */
    public function findCookiesPage(array $website, string $locale): ?array
	{
        $pages = $this->createQueryBuilder('p')
            ->leftJoin('p.urls', 'u')
            ->leftJoin('u.website', 'w')
            ->leftJoin('w.configuration', 'c')
            ->leftJoin('c.domains', 'd')
            ->andWhere('p.website = :website')
            ->andWhere('u.locale = :locale')
            ->andWhere('u.code LIKE :code')
            ->setParameter('code', '%cookies%')
            ->setParameter('locale', $locale)
            ->setParameter('website', $website['id'])
            ->addSelect('u')
            ->addSelect('w')
            ->addSelect('c')
            ->addSelect('d')
            ->getQuery()
            ->getArrayResult();

        return $pages && count($pages) === 1 ? $pages[0] : NULL;
    }

	/**
	 * Find by Action
	 *
	 * @param mixed $website
	 * @param string $locale
	 * @param string $classname
	 * @param int $filterId
	 * @param string|null $slugAction
	 * @param string|null $hasArray
	 * @return mixed
	 */
    public function findByAction(
    	$website,
        string $locale,
        string $classname,
        int $filterId,
        string $slugAction = NULL,
        string $hasArray = NULL)
    {
		$websiteId = $website instanceof Website ? $website->getId() : $website['id'];

        $queryBuilder = $this->createQueryBuilder('p')
            ->leftJoin('p.urls', 'u')
            ->leftJoin('p.website', 'w')
            ->leftJoin('p.layout', 'l')
            ->leftJoin('l.zones', 'z')
            ->leftJoin('z.cols', 'c')
            ->leftJoin('c.blocks', 'b')
            ->leftJoin('b.i18ns', 'bi')
            ->leftJoin('b.action', 'a')
            ->leftJoin('b.actionI18ns', 'ai')
            ->andWhere('p.website = :website')
            ->andWhere('u.locale = :locale')
            ->setParameter('locale', $locale)
            ->setParameter('website', $websiteId)
            ->addSelect('u');

        /** Find by action & filter */
		$pageQb = $queryBuilder
            ->andWhere('a.entity = :entity')
            ->andWhere('ai.actionFilter = :actionFilter')
            ->setParameter('entity', $classname)
            ->setParameter('actionFilter', $filterId)
			->getQuery();

        if($hasArray) {
			$page = $pageQb->getArrayResult();
		} else {
			$page = $pageQb->getResult();
		}

        if (!$page && $slugAction) {
            /** Find by action & filter */
			$pageQb = $queryBuilder->andWhere('a.slug = :slug')
                ->setParameter('slug', $slugAction)
                ->getQuery();
			if($hasArray) {
				$page = $pageQb->getArrayResult();
			} else {
				$page = $pageQb->getResult();
			}
        }

        return !empty($page[0]) ? $page[0] : NULL;
    }

    /**
     * Find by Action slug
     *
     * @param Website $website
     * @param string $locale
     * @param string $slug
     * @return Page[]
     */
    public function findBySlugAction(Website $website, string $locale, string $slug): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.urls', 'u')
            ->leftJoin('p.website', 'w')
            ->leftJoin('p.layout', 'l')
            ->leftJoin('l.zones', 'z')
            ->leftJoin('z.cols', 'c')
            ->leftJoin('c.blocks', 'b')
            ->leftJoin('b.i18ns', 'bi')
            ->leftJoin('b.action', 'a')
            ->leftJoin('b.actionI18ns', 'ai')
            ->andWhere('p.website = :website')
            ->andWhere('u.locale = :locale')
            ->andWhere('a.slug = :slug')
            ->setParameter('locale', $locale)
            ->setParameter('website', $website)
            ->setParameter('slug', $slug)
            ->addSelect('u')
            ->addSelect('w')
            ->addSelect('l')
            ->addSelect('z')
            ->addSelect('c')
            ->addSelect('b')
            ->addSelect('bi')
            ->addSelect('a')
            ->addSelect('ai')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find one by Action slug
     *
     * @param Website $website
     * @param string $locale
     * @param string $slug
     * @return Page|null
     */
    public function findOneBySlugAction(Website $website, string $locale, string $slug): ?Page
    {
        $pages = $this->findBySlugAction($website, $locale, $slug);
        return $pages ? $pages[0] : NULL;
    }

    /**
     * Find by Website order AdminName
     *
     * @param Website $website
     * @return QueryBuilder
     */
    public function findByWebsiteAlphaQueryBuilder(Website $website): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.website = :website')
            ->setParameter('website', $website)
            ->orderBy('p.adminName', 'ASC');
    }

    /**
     * Find by Website order AdminName
     *
     * @param Website $website
     * @return Page[]|null
     */
    public function findByWebsiteAlpha(Website $website): ?array
    {
        return $this->findByWebsiteAlphaQueryBuilder($website)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find by Website
     *
     * @param Website $website
     * @return Page[]|null
     */
    public function findByWebsite(Website $website): ?array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.website = :website')
            ->setParameter('website', $website)
            ->orderBy('p.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find locale Url by Page
     *
     * @param string $locale
     * @param Page|null $page
     * @return Url|null
     * @throws NonUniqueResultException
     */
    public function findOneUrlByPageAndLocale(string $locale, Page $page = NULL): ?Url
    {
        if ($page) {

            $result = $this->createQueryBuilder('p')
                ->leftJoin('p.urls', 'u')
                ->andWhere('p.id = :id')
                ->andWhere('u.locale = :locale')
                ->setParameter('id', $page->getId())
                ->setParameter('locale', $locale)
                ->getQuery()
                ->getOneOrNullResult();

            if ($result && !$result->getUrls()->isEmpty() && !empty($result->getUrls()[0]->getCode())) {
                foreach ($result->getUrls() as $url) {
                    /** @var Url $url */
                    if ($url->getLocale() === $locale && $url->getIsOnline() && $url->getCode()) {
                        return $url;
                    }
                }
            }
        }
    }

    /**
     * Find by Block
     *
     * @param Block $block
     * @return Page|null
     * @throws NonUniqueResultException
     */
    public function findByBlock(Block $block): ?Page
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.layout', 'l')
            ->leftJoin('l.zones', 'z')
            ->leftJoin('z.cols', 'c')
            ->leftJoin('c.blocks', 'b')
            ->andWhere('b.id = :id')
            ->setParameter('id', $block->getId())
            ->addSelect('l')
            ->addSelect('z')
            ->addSelect('c')
            ->addSelect('b')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find by parent Page
     *
     * @param Page $page
     * @param string $locale
     * @return Page[]
     */
    public function findOnlineAndLocaleByParent(Page $page, string $locale): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.urls', 'u')
            ->andWhere('p.parent = :parent')
            ->andWhere('u.isOnline = :isOnline')
            ->andWhere('u.locale = :locale')
            ->setParameter('parent', $page)
            ->setParameter('isOnline', true)
            ->setParameter('locale', $locale)
            ->addSelect('u')
            ->orderBy('p.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Front optimized QueryBuilder
     *
     * @param mixed $website
     * @param string $locale
     * @param bool $offline
     * @param bool $hasArray
     * @return QueryBuilder
     */
    public function optimizedQueryBuilder($website, string $locale, bool $offline = false, bool $hasArray = false): QueryBuilder
    {
        $website = $website instanceof Website ? $website : $website['id'];

        $queryBuilder = $this->createQueryBuilder('p')
            ->leftJoin('p.urls', 'u')
            ->leftJoin('p.website', 'w')
            ->leftJoin('w.configuration', 'c')
            ->leftJoin('c.domains', 'd')
            ->andWhere('u.website = :website')
            ->andWhere('u.locale = :locale')
            ->setParameter('locale', $locale)
            ->setParameter('website', $website)
            ->addSelect('u')
            ->addSelect('w')
            ->addSelect('c')
            ->addSelect('d');

        if($hasArray) {
            $queryBuilder->leftJoin('p.pages', 'pg')
				->leftJoin('p.layout', 'l')
//				->leftJoin('l.zones', 'z')
                ->leftJoin('pg.urls', 'pgu')
                ->leftJoin('u.website', 'uw')
                ->leftJoin('u.seo', 'us')
                ->leftJoin('us.mediaRelation', 'usm')
                ->leftJoin('usm.media', 'usmm')
                ->leftJoin('uw.information', 'uwi')
                ->leftJoin('uw.configuration', 'uwc')
                ->leftJoin('uw.seoConfiguration', 'uws')
                ->addSelect('l')
                ->addSelect('pg')
                ->addSelect('pgu')
                ->addSelect('us')
                ->addSelect('usm')
                ->addSelect('usmm')
                ->addSelect('uw')
                ->addSelect('uwi')
                ->addSelect('uwc')
                ->addSelect('uws');
        }

        if (!$offline) {
            $queryBuilder->andWhere('p.publicationStart IS NULL OR p.publicationStart < CURRENT_TIMESTAMP()')
                ->andWhere('p.publicationEnd IS NULL OR p.publicationEnd > CURRENT_TIMESTAMP()')
                ->andWhere('u.isOnline = :isOnline')
                ->setParameter('isOnline', true);
        }

        return $queryBuilder;
    }
}