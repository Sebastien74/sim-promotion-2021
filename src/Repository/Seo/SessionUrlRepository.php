<?php

namespace App\Repository\Seo;

use App\Entity\Seo\Session;
use App\Entity\Seo\SessionUrl;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * SessionUrlRepository
 *
 * @method SessionUrl|null find($id, $lockMode = null, $lockVersion = null)
 * @method SessionUrl|null findOneBy(array $criteria, array $orderBy = null)
 * @method SessionUrl[]    findAll()
 * @method SessionUrl[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SessionUrlRepository extends ServiceEntityRepository
{
    /**
     * SessionUrlRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SessionUrl::class);
    }


    /**
     * Find last url record by URI
     *
     * @param Session $session
     * @param string|null $uri
     * @return SessionUrl|null
     * @throws NonUniqueResultException
     */
    public function findLastByUri(Session $session, $uri = NULL): ?SessionUrl
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.session = :session')
            ->andWhere('u.uri = :uri')
            ->setParameter('session', $session)
            ->setParameter('uri', $uri)
            ->setMaxResults(1)
            ->orderBy('u.createdAt', 'DESC')
            ->getQuery()
            ->getOneOrNullResult();
    }

}
