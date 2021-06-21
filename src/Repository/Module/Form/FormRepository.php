<?php

namespace App\Repository\Module\Form;

use App\Entity\Module\Form\Form;
use App\Entity\Core\Website;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

/**
 * FormRepository
 *
 * @method Form|null find($id, $lockMode = null, $lockVersion = null)
 * @method Form|null findOneBy(array $criteria, array $orderBy = null)
 * @method Form[]    findAll()
 * @method Form[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FormRepository extends ServiceEntityRepository
{
    /**
     * FormRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Form::class);
    }

    /**
     * Find one by filter
     *
     * @param Website $website
     * @param string $locale
     * @param string|int $filter
     * @return Form|null
     */
    public function findOneByFilter(Website $website, string $locale, $filter): ?Form
    {
        $statement = $this->createQueryBuilder('f')
            ->leftJoin('f.website', 'w')
            ->leftJoin('w.configuration', 'co')
            ->leftJoin('f.layout', 'l')
            ->leftJoin('l.zones', 'z')
            ->leftJoin('z.cols', 'c')
            ->leftJoin('c.blocks', 'b')
            ->leftJoin('b.fieldConfiguration', 'fc')
            ->leftJoin('b.blockType', 'bt')
            ->leftJoin('b.i18ns', 'bi')
            ->leftJoin('b.actionI18ns', 'bai')
            ->leftJoin('fc.fieldValues', 'fcv')
            ->leftJoin('fcv.i18ns', 'fcvi')
            ->andWhere('f.website = :website')
            ->setParameter('website', $website)
            ->addSelect('w')
            ->addSelect('co')
            ->addSelect('l')
            ->addSelect('z')
            ->addSelect('c')
            ->addSelect('b')
            ->addSelect('fc')
            ->addSelect('bt')
            ->addSelect('bi')
            ->addSelect('bai')
            ->addSelect('fcv')
            ->addSelect('fcvi');

        if (is_numeric($filter)) {
            $statement->andWhere('f.id = :id')
                ->setParameter('id', $filter);
        } else {
            $statement->andWhere('f.slug = :slug')
                ->setParameter('slug', $filter);
        }

        return $statement->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find admin index
     *
     * @param Website $website
     * @param Request $request
     * @return Form[]
     */
    public function findAdminIndex(Website $website, Request $request)
    {
        $statement = $this->createQueryBuilder('f')
            ->leftJoin('f.website', 'w')
            ->andWhere('f.website = :website')
            ->setParameter('website', $website)
            ->addSelect('w');

        $stepForm = $request->get('stepform');

        if ($stepForm) {
            $statement->andWhere('f.stepform = :stepform')
                ->setParameter('stepform', $stepForm);
        } else {
            $statement->andWhere('f.stepform IS NULL');
        }

        return $statement->orderBy('f.position', 'ASC')
            ->getQuery()
            ->getResult();
    }
}