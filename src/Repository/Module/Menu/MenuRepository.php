<?php

namespace App\Repository\Module\Menu;

use App\Entity\Module\Menu\Menu;
use App\Entity\Core\Website;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * MenuRepository
 *
 * @method Menu|null find($id, $lockMode = null, $lockVersion = null)
 * @method Menu|null findOneBy(array $criteria, array $orderBy = null)
 * @method Menu[]    findAll()
 * @method Menu[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class MenuRepository extends ServiceEntityRepository
{
    /**
     * MenuRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Menu::class);
    }

	/**
	 * Find Menu as array
	 *
	 * @param int $id
	 * @return array
	 */
    public function findArray(int $id): array
    {
		$result = $this->createQueryBuilder('m')
			->andWhere('m.id = :id')
			->setParameter('id', $id)
			->getQuery()
			->getArrayResult();

		return !empty($result[0]) ? $result[0] : [];
	}

    /**
     * Find by filter
     *
     * @param Website $website
     * @param string|int $filter
     * @param string $locale
     * @return array
     */
    public function findOneByFilter(Website $website, $filter, string $locale): array
    {
        $statement = $this->createQueryBuilder('m')
            ->leftJoin('m.website', 'w')
            ->leftJoin('m.links', 'l')
            ->leftJoin('l.mediaRelation', 'mr')
            ->leftJoin('l.links', 'li')
            ->leftJoin('li.i18n', 'i')
            ->leftJoin('w.configuration', 'c')
            ->andWhere('m.website = :website')
            ->andWhere('l.locale = :locale')
            ->setParameter('website', $website->getId())
            ->setParameter('locale', $locale)
            ->addSelect('w')
            ->addSelect('l')
            ->addSelect('mr')
            ->addSelect('li')
            ->addSelect('i')
            ->addSelect('c');

        if (is_numeric($filter)) {
            $statement->andWhere('m.id = :id')
                ->setParameter('id', $filter);
        } else {
            $statement->andWhere('m.slug = :slug')
                ->setParameter('slug', $filter);
        }

        $result = $statement->getQuery()->getArrayResult();

        return !empty($result[0]) ? $result[0] : [];
    }

    /**
     * Find main
     *
     * @param Website $website
     * @param string $locale
     * @return array
     */
    public function findMain(Website $website, string $locale): array
	{
        $result = $this->createQueryBuilder('m')
            ->leftJoin('m.website', 'w')
            ->leftJoin('m.links', 'l')
            ->leftJoin('l.mediaRelation', 'mr')
            ->leftJoin('l.links', 'li')
            ->leftJoin('li.i18n', 'i')
            ->leftJoin('w.configuration', 'c')
            ->andWhere('m.website = :website')
            ->andWhere('m.isMain = :isMain')
            ->andWhere('l.locale = :locale')
            ->setParameter('website', $website->getId())
            ->setParameter('locale', $locale)
            ->setParameter('isMain', true)
            ->addSelect('w')
            ->addSelect('l')
            ->addSelect('mr')
            ->addSelect('li')
            ->addSelect('i')
            ->addSelect('c')
            ->getQuery()
            ->getArrayResult();

        return !empty($result[0]) ? $result[0] : [];
    }

    /**
     * Find footer
     *
     * @param Website $website
     * @param string $locale
	 * @return array
     */
    public function findFooter(Website $website, string $locale): array
	{
		$result = $this->createQueryBuilder('m')
            ->leftJoin('m.website', 'w')
            ->leftJoin('m.links', 'l')
            ->leftJoin('l.mediaRelation', 'mr')
            ->leftJoin('l.links', 'li')
            ->leftJoin('li.i18n', 'i')
            ->leftJoin('w.configuration', 'c')
            ->andWhere('m.website = :website')
            ->andWhere('m.isFooter = :isFooter')
            ->andWhere('l.locale = :locale')
			->setParameter('website', $website->getId())
            ->setParameter('locale', $locale)
            ->setParameter('isFooter', true)
            ->addSelect('w')
            ->addSelect('l')
            ->addSelect('mr')
            ->addSelect('li')
            ->addSelect('i')
            ->addSelect('c')
            ->getQuery()
            ->getArrayResult();

		return !empty($result[0]) ? $result[0] : [];
    }

    /**
     * Find Undefined Menu[]
     *
     * @param Website $website
     * @param string $locale
     * @return Menu|null
     */
    public function findOptimized(Website $website, string $locale): ?array
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.website', 'w')
            ->leftJoin('m.links', 'l')
            ->leftJoin('l.mediaRelation', 'mr')
            ->leftJoin('l.links', 'li')
            ->leftJoin('li.i18n', 'i')
            ->leftJoin('w.configuration', 'c')
            ->andWhere('m.website = :website')
            ->andWhere('l.locale = :locale')
            ->setParameter('website', $website->getId())
            ->setParameter('locale', $locale)
            ->addSelect('w')
            ->addSelect('l')
            ->addSelect('mr')
            ->addSelect('li')
            ->addSelect('i')
            ->addSelect('c')
            ->getQuery()
            ->getArrayResult();
    }
}
