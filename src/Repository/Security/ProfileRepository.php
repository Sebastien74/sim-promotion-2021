<?php

namespace App\Repository\Security;

use App\Entity\Security\Profile;
use App\Entity\Security\User;
use App\Entity\Security\UserFront;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * ProfileRepository
 *
 * @method Profile|null find($id, $lockMode = null, $lockVersion = null)
 * @method Profile|null findOneBy(array $criteria, array $orderBy = null)
 * @method Profile[]    findAll()
 * @method Profile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ProfileRepository extends ServiceEntityRepository
{
    /**
     * ProfileRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Profile::class);
    }

    /**
     * Check if Address by slug exist
     *
     * @param UserFront|User $user
     * @param string $slug
     * @param bool $hasObject
     * @return bool
     */
    public function addressExist($user, string $slug, $hasObject = false)
    {
        $profile = $user->getProfile();

        if ($profile instanceof Profile) {
            foreach ($profile->getAddresses() as $address) {
                if ($address->getSlug() === $slug) {
                    return $hasObject ? $address : true;
                }
            }
        }

        return false;
    }
}
