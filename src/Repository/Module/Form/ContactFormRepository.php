<?php

namespace App\Repository\Module\Form;

use App\Entity\Module\Form\ContactForm;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * ContactFormRepository
 *
 * @method ContactForm|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContactForm|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContactForm[]    findAll()
 * @method ContactForm[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ContactFormRepository extends ServiceEntityRepository
{
    /**
     * ContactFormRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContactForm::class);
    }
}
