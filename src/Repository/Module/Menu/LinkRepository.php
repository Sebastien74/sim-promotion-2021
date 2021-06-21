<?php

namespace App\Repository\Module\Menu;

use App\Entity\Module\Menu\Link;
use App\Entity\Module\Menu\Menu;
use App\Entity\Core\Website;
use App\Entity\Layout\Page;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * LinkRepository
 *
 * @method Link|null find($id, $lockMode = null, $lockVersion = null)
 * @method Link|null findOneBy(array $criteria, array $orderBy = null)
 * @method Link[]    findAll()
 * @method Link[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class LinkRepository extends ServiceEntityRepository
{
    /**
     * LinkRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Link::class);
    }

    /**
     * Find by Menu and locale
     *
     * @param array $menu
     * @param string $locale
     * @return Link[]
     */
    public function findByMenuAndLocale(array $menu, string $locale): array
    {
        return $this->createQueryBuilder('l')
            ->leftJoin('l.menu', 'm')
            ->leftJoin('l.parent', 'p')
            ->leftJoin('l.i18n', 'i')
            ->leftJoin('i.website', 'iw')
            ->leftJoin('i.targetPage', 'it')
            ->leftJoin('it.website', 'itw')
            ->leftJoin('it.urls', 'itu')
            ->leftJoin('itw.configuration', 'itc')
            ->leftJoin('l.mediaRelation', 'mr')
            ->leftJoin('mr.media', 'md')
            ->andWhere('l.menu = :menu')
            ->andWhere('l.locale = :locale')
            ->andWhere('i.locale = :locale')
            ->setParameter('menu', $menu['id'])
            ->setParameter('locale', $locale)
            ->orderBy('l.position', 'ASC')
            ->addSelect('m')
            ->addSelect('p')
            ->addSelect('it')
            ->addSelect('itw')
            ->addSelect('itu')
            ->addSelect('itc')
            ->addSelect('i')
            ->addSelect('iw')
            ->addSelect('mr')
            ->addSelect('md')
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * Find by Menu and locale
     *
     * @param Website $website
     * @param Page $page
     * @param string $locale
     * @return Link[]
     */
    public function findByPageAndLocale(Website $website, Page $page, string $locale)
    {
        return $this->createQueryBuilder('l')
            ->leftJoin('l.i18n', 'i')
            ->andWhere('i.locale = :locale')
            ->andWhere('i.targetPage = :page')
            ->andWhere('i.website = :website')
            ->setParameter('locale', $locale)
            ->setParameter('page', $page)
            ->setParameter('website', $website)
            ->addSelect('i')
            ->getQuery()
            ->getResult();
    }
}
