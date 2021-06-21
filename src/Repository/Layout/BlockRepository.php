<?php

namespace App\Repository\Layout;

use App\Entity\Layout\Block;
use App\Entity\Layout\Layout;
use App\Entity\Layout\Page;
use App\Entity\Translation\i18n;
use App\Helper\Core\InterfaceHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * BlockRepository
 *
 * @method Block|null find($id, $lockMode = null, $lockVersion = null)
 * @method Block|null findOneBy(array $criteria, array $orderBy = null)
 * @method Block[]    findAll()
 * @method Block[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @property InterfaceHelper $interfaceHelper
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class BlockRepository extends ServiceEntityRepository
{
    /**
     * BlockRepository constructor.
     *
     * @param ManagerRegistry $registry
     * @param InterfaceHelper $interfaceHelper
     */
    public function __construct(ManagerRegistry $registry, InterfaceHelper $interfaceHelper)
    {
        $this->interfaceHelper = $interfaceHelper;

        parent::__construct($registry, Block::class);
    }

    /**
     * Find Block by titleForce, locale & Page
     *
     * @param mixed $entity
     * @param string $locale
     * @param int|null $titleForce
     * @param bool $all
     * @return string|array
     */
    public function findTitleByForceAndLocalePage($entity, string $locale, int $titleForce = NULL, bool $all = false)
    {
        if($entity->getLayout() instanceof Layout) {

            /** @var Block[] $results */
            $statement = $this->createQueryBuilder('b')
                ->leftJoin('b.i18ns', 'i')
                ->leftJoin('b.col', 'c')
                ->leftJoin('c.zone', 'z')
                ->leftJoin('z.layout', 'l')
                ->andWhere('i.titleForce = :titleForce')
                ->andWhere('i.title IS NOT NULL')
                ->andWhere('i.locale = :locale')
                ->andWhere('l.id = :layoutId')
                ->setParameter('titleForce', $titleForce)
                ->setParameter('locale', $locale)
                ->setParameter('layoutId', $entity->getLayout()->getId())
                ->addSelect('i')
                ->addSelect('c')
                ->addSelect('z')
                ->addSelect('l')
                ->addOrderBy('b.position', 'ASC')
                ->addOrderBy('z.position', 'ASC');

            if (!$all) {
                $statement->setMaxResults(1);
            }

            $results = $statement->getQuery()
                ->getResult();

            if ($all) {
                return $results;
            }

            /** @var Block $result */
            $result = $results ? $results[0] : NULL;

            return $result ? $result->getI18ns()[0]->getTitle() : NULL;
        }
    }

    /**
     * Find block by titleForce, locale & Layout
     *
     * @param mixed $layout
     * @param string $locale
     * @param int $titleForce
     * @param bool $all
     * @param bool $hasArray
     * @return mixed
     */
    public function findTitleByForceAndLocaleLayout($layout, string $locale, int $titleForce, bool $all = false, bool $hasArray = false)
    {
        $layoutId = is_array($layout) ? $layout['id'] : $layout->getId();

        $results = $this->createQueryBuilder('b')
            ->leftJoin('b.i18ns', 'i')
            ->leftJoin('b.col', 'c')
            ->leftJoin('c.zone', 'z')
            ->leftJoin('z.layout', 'l')
            ->andWhere('i.titleForce = :titleForce')
            ->andWhere('i.title IS NOT NULL')
            ->andWhere('i.locale = :locale')
            ->andWhere('l.id = :layoutId')
            ->setParameter('titleForce', $titleForce)
            ->setParameter('locale', $locale)
            ->setParameter('layoutId', $layoutId)
            ->addSelect('i')
            ->addOrderBy('b.position', 'ASC')
            ->addOrderBy('z.position', 'ASC')
            ->setMaxResults(1)
            ->getQuery();

        if($hasArray) {
            $results = $results->getArrayResult();
        } else {
            $results = $results->getResult();
        }

        /** @var Block $result */
        $result = $results ? $results[0] : NULL;

        $i18nResult = NULL;
        if (is_object($result) && method_exists($result, 'getI18ns')
            || $hasArray && !empty($result['i18ns'])) {
            $i18ns = $hasArray ? $result['i18ns'] : $result->getI18ns();
            foreach ($i18ns as $i18n) {
                $i18nLocale = $hasArray ? $i18n['locale'] : $i18n->getLocale();
                if ($i18nLocale === $locale) {
                    $i18nResult = $i18n;
                    break;
                }
            }
        }

        if ($result && $all) {
            return $i18nResult;
        } elseif ($i18nResult instanceof i18n) {
            return $i18nResult->getTitle();
        } elseif ($hasArray && !empty($i18nResult['title'])) {
            return $i18nResult['title'];
        }

        return $i18nResult instanceof i18n ? $i18nResult->getTitle() : NULL;
    }

    /**
     * Find block by titleForce, locale & Page
     *
     * @param mixed $layout
     * @param string $locale
     * @param string $blockType
     * @return array
     */
    public function findByBlockTypeAndLocaleLayout($layout, string $blockType, string $locale): array
	{
	    $layoutId = is_object($layout) ? $layout->getId() : $layout['id'];

        $results = $this->createQueryBuilder('b')
            ->leftJoin('b.blockType', 'bt')
            ->leftJoin('b.i18ns', 'i')
            ->leftJoin('b.col', 'c')
            ->leftJoin('c.zone', 'z')
            ->leftJoin('z.layout', 'l')
            ->andWhere('bt.slug = :slug')
            ->andWhere('i.locale = :locale')
            ->andWhere('l.id = :layoutId')
            ->setParameter('slug', $blockType)
            ->setParameter('locale', $locale)
            ->setParameter('layoutId', $layoutId)
            ->addSelect('i')
            ->addSelect('c')
            ->addSelect('z')
            ->addSelect('l')
            ->addOrderBy('b.position', 'ASC')
            ->addOrderBy('z.position', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getArrayResult();

        return !empty($results[0]) ? $results[0] : [];
    }

    /**
     * Find block text by locale & Page
     *
     * @param string $field
     * @param Page $page
     * @param string $locale
     * @return Block|null
     * @throws NonUniqueResultException
     */
    public function findFieldTextByLocalePage(string $field, Page $page, string $locale)
    {
        $result = $this->createQueryBuilder('b')
            ->leftJoin('b.blockType', 'bt')
            ->leftJoin('b.i18ns', 'i')
            ->leftJoin('b.col', 'c')
            ->leftJoin('c.zone', 'z')
            ->leftJoin('z.layout', 'l')
            ->leftJoin('l.page', 'p')
            ->andWhere('bt.slug = :slug')
            ->andWhere('i.' . $field . ' IS NOT NULL')
            ->andWhere('i.locale = :locale')
            ->andWhere('p.id = :page')
            ->setParameter('slug', 'media')
            ->setParameter('locale', $locale)
            ->setParameter('page', $page)
            ->addSelect('bt')
            ->addSelect('i')
            ->addSelect('l')
            ->addSelect('z')
            ->addSelect('c')
            ->addOrderBy('b.position', 'ASC')
            ->addOrderBy('z.position', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        $getter = 'get' . ucfirst($field);

        return $result ? $result->getI18ns()[0]->$getter() : NULL;
    }

    /**
     * Find block text by locale & Page
     *
     * @param Page $page
     * @param string $locale
     * @return array
     */
    public function findMediaByLocalePage(Page $page, string $locale): array
	{
        $result = $this->createQueryBuilder('b')
            ->leftJoin('b.mediaRelations', 'mr')
            ->leftJoin('mr.media', 'm')
            ->leftJoin('b.col', 'c')
            ->leftJoin('c.zone', 'z')
            ->leftJoin('z.layout', 'l')
            ->leftJoin('l.page', 'p')
            ->andWhere('m.filename IS NOT NULL')
            ->andWhere('m.screen = :screen')
            ->andWhere('mr.locale = :locale')
            ->andWhere('p.id = :page')
            ->setParameter('locale', $locale)
            ->setParameter('page', $page)
            ->setParameter('screen', 'desktop')
            ->addSelect('mr')
            ->addSelect('m')
            ->addSelect('l')
            ->addSelect('z')
            ->addSelect('c')
            ->addOrderBy('b.position', 'ASC')
            ->addOrderBy('z.position', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getArrayResult();

        return !empty($result[0]) ? $result[0]['mediaRelations'][0]['media'] : [];
    }
}