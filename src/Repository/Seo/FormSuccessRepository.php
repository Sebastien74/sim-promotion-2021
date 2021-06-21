<?php

namespace App\Repository\Seo;

use App\Entity\Seo\FormSuccess;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * FormSuccessRepository
 *
 * @method FormSuccess|null find($id, $lockMode = null, $lockVersion = null)
 * @method FormSuccess|null findOneBy(array $criteria, array $orderBy = null)
 * @method FormSuccess[]    findAll()
 * @method FormSuccess[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FormSuccessRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FormSuccess::class);
    }
}
