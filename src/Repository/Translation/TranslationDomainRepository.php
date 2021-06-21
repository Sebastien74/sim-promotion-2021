<?php

namespace App\Repository\Translation;

use App\Entity\Translation\TranslationDomain;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * TranslationDomainRepository
 *
 * @method TranslationDomain|null find($id, $lockMode = null, $lockVersion = null)
 * @method TranslationDomain|null findOneBy(array $criteria, array $orderBy = null)
 * @method TranslationDomain[]    findAll()
 * @method TranslationDomain[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TranslationDomainRepository extends ServiceEntityRepository
{
    /**
     * TranslationDomainRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TranslationDomain::class);
    }

    /**
     * Get All Domains
     *
     * @return TranslationDomain[]
     */
    public function findAllDomains()
    {
        return $this->createQueryBuilder('d')
            ->leftJoin('d.units', 'u')
            ->leftJoin('u.translations', 't')
            ->orderBy('d.adminName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get Domain
     *
     * @param int $id
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function findDomain(int $id)
    {
        return $this->createQueryBuilder('d')
            ->leftJoin('d.units', 'u')
            ->leftJoin('u.translations', 't')
            ->andWhere('d.id = :id')
            ->setParameter('id', $id)
            ->addSelect('u')
            ->addSelect('t')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get Domains by search
     *
     * @param string|null $search
     * @return array
     */
    public function findBySearch(string $search = NULL)
    {
        return $this->createQueryBuilder('d')
            ->leftJoin('d.units', 'u')
            ->leftJoin('u.translations', 't')
            ->andWhere('u.keyName LIKE :search')
            ->orWhere('t.content LIKE :search')
            ->setParameter('search', '%' . trim($search) . '%')
            ->addSelect('u')
            ->getQuery()
            ->getResult();
    }
}
