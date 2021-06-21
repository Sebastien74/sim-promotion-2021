<?php

namespace App\Repository\Module\Newscast;

use App\Entity\Module\Newscast\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * CommentRepository
 *
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @property Request $request
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CommentRepository extends ServiceEntityRepository
{
    private $request;

    /**
     * CommentRepository constructor.
     *
     * @param ManagerRegistry $registry
     * @param RequestStack $requestStack
     */
    public function __construct(ManagerRegistry $registry, RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();

        parent::__construct($registry, Comment::class);
    }
}
