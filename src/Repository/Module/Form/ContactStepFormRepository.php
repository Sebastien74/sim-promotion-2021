<?php

namespace App\Repository\Module\Form;

use App\Entity\Module\Form\ContactStepForm;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * ContactStepFormRepository
 *
 * @method ContactStepForm|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContactStepForm|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContactStepForm[]    findAll()
 * @method ContactStepForm[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ContactStepFormRepository extends ServiceEntityRepository
{
    /**
     * ContactStepFormRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContactStepForm::class);
    }
}
