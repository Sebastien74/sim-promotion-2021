<?php

namespace App\Repository\Security;

use App\Entity\Security\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * UserRepository
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class UserRepository extends ServiceEntityRepository
{
    /**
     * UserRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Find User Email order alphabetical
     *
     * @return User[]
     */
    public function findAllEmailAlphabetical()
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.email', 'ASC')
            ->getQuery()
            ->execute();
    }

    /**
     * Find User roles
     *
     * @param User $user
     * @return User[]
     * @throws NonUniqueResultException
     */
    public function findRoles(User $user)
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.groups', 'g')
            ->leftJoin('g.roles', 'r')
            ->andWhere('u.id = :id')
            ->setParameter('id', $user->getId())
            ->addSelect('g')
            ->addSelect('r')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find user by email LIKE
     *
     * @param string $query
     * @param int $limit
     * @return User[]
     */
    public function findAllMatching(string $query, int $limit = 5)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.email LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find User for switcher selector
     *
     * @return User[]
     */
    public function findForSwitcher()
    {
        $excluded = ['ROLE_INTERNAL'];

        $users = $this->createQueryBuilder('u')
            ->orderBy('u.login', 'ASC')
            ->getQuery()
            ->getResult();

        foreach ($users as $key => $user) {
            /** @var User $user */
            foreach ($user->getRoles() as $role) {
                if (in_array($role, $excluded)) {
                    unset($users[$key]);
                }
            }
        }

        return $users;
    }

    /**
     * Find user by login or email
     *
     * @param string $login
     * @param string $email
     * @return User|null
     * @throws NonUniqueResultException
     */
    public function findExisting(string $login, string $email)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.login = :login')
            ->orWhere('u.email = :email')
            ->setParameter('login', $login)
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find users with token not NULL
     */
    public function findHaveToken()
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.token IS NOT NULL')
            ->andWhere('u.tokenRequest IS NOT NULL')
            ->getQuery()
            ->getResult();
    }
}
