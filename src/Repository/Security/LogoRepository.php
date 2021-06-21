<?php

namespace App\Repository\Security;

use App\Entity\Security\Logo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * LogoRepository
 *
 * @method Logo|null find($id, $lockMode = null, $lockVersion = null)
 * @method Logo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Logo[]    findAll()
 * @method Logo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class LogoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Logo::class);
    }
}