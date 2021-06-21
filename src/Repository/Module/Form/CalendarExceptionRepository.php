<?php

namespace App\Repository\Module\Form;

use App\Entity\Module\Form\CalendarException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * CalendarExceptionRepository
 *
 * @method CalendarException|null find($id, $lockMode = null, $lockVersion = null)
 * @method CalendarException|null findOneBy(array $criteria, array $orderBy = null)
 * @method CalendarException[]    findAll()
 * @method CalendarException[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CalendarExceptionRepository extends ServiceEntityRepository
{
    /**
     * CalendarExceptionRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CalendarException::class);
    }
}
