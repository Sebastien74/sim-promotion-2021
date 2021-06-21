<?php

namespace App\Repository\Module\Form;

use App\Entity\Module\Form\ContactValue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * ContactValueRepository
 *
 * @method ContactValue|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContactValue|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContactValue[]    findAll()
 * @method ContactValue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ContactValueRepository extends ServiceEntityRepository
{
    /**
     * ContactValueRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContactValue::class);
    }
}
