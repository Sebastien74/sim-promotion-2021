<?php

namespace App\Twig\Core;

use App\Entity\Core\Website;
use App\Entity\Security\Picture;
use App\Entity\Security\Profile;
use App\Entity\Security\User;
use App\Entity\Security\UserFront;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * SecurityRuntime
 *
 * @property KernelInterface $kernel
 * @property EntityManagerInterface $entityManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SecurityRuntime implements RuntimeExtensionInterface
{
    private $kernel;
    private $entityManager;

    /**
     * SecurityRuntime constructor.
     *
     * @param KernelInterface $kernel
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(KernelInterface $kernel, EntityManagerInterface $entityManager)
    {
        $this->kernel = $kernel;
        $this->entityManager = $entityManager;
    }

    /**
     * Get User|UserFront Profile image
     *
     * @param null|User|UserFront $user
     * @return string|null
     */
    public function getProfileImg($user = NULL): string
    {
        if ($user instanceof User || $user instanceof UserFront) {

            $picture = $user->getPicture();
            $dirname = $picture instanceof Picture && $picture->getDirname() ? $picture->getDirname() : NULL;
            $filesystem = new Filesystem();

            if ($dirname && $filesystem->exists($this->kernel->getProjectDir() . DIRECTORY_SEPARATOR . 'public' . $dirname)) {
                return $dirname;
            }
        }

        return 'medias/anonymous.png';
    }

    /**
     * Get User|UserFront Profile Address[]
     *
     * @param null|User|UserFront $user
     * @return array
     */
    public function getProfileAddresses($user = NULL): array
    {
        $addresses = [];

        if ($user instanceof User || $user instanceof UserFront) {
            $profile = $user->getProfile();
            if ($profile instanceof Profile) {
                foreach ($profile->getAddresses() as $address) {
                    $addresses[$address->getSlug()] = $address;
                }
            }
        }

        return $addresses;
    }

    /**
     * Get User|UserFront Profile Address[]
     *
     * return string
     * @param Website $website
     * @param string $type
     * @return array
     */
    public function getOnlineUsers(Website $website, string $type): array
    {
        $userClass = $type === 'admin' ? User::class : UserFront::class;
        $delay = new \DateTime();
        $delay->setTimestamp(strtotime('2 minutes ago'));

        $qb = $this->entityManager->getRepository($userClass)->createQueryBuilder('u')
            ->andWhere('u.lastActivity > :delay')
            ->setParameter('delay', $delay);

        if ($userClass === UserFront::class) {
            $qb->andWhere('u.website' . ' = :website')
                ->setParameter(':website', $website);
        }

        return $qb->getQuery()->getResult();
    }
}