<?php

namespace App\Repository\Core;

use App\Entity\Core\ScheduledCommand;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\TransactionRequiredException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * ScheduledCommandRepository
 *
 * @method ScheduledCommand|null find($id, $lockMode = null, $lockVersion = null)
 * @method ScheduledCommand|null findOneBy(array $criteria, array $orderBy = null)
 * @method ScheduledCommand[]    findAll()
 * @method ScheduledCommand[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ScheduledCommandRepository extends ServiceEntityRepository
{
    /**
     * ScheduledCommandRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ScheduledCommand::class);
    }

    /**
     * Find all enabled command ordered by priority
     *
     * @return ScheduledCommand[]
     */
    public function findEnabledCommand()
    {
        return $this->findBy(array('disabled' => false, 'locked' => false), array('priority' => 'DESC'));
    }

    /**
     * Find all locked commands
     *
     * @return ScheduledCommand[]
     */
    public function findLockedCommand()
    {
        return $this->findBy(array('disabled' => false, 'locked' => true), array('priority' => 'DESC'));
    }

    /**
     * Find all failed command
     *
     * @return ScheduledCommand[]
     */
    public function findFailedCommand()
    {
        return $this->createQueryBuilder('command')
            ->where('command.disabled = false')
            ->andWhere('command.lastReturnCode != 0')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param integer|bool $lockTimeout
     * @return array|ScheduledCommand[]
     */
    public function findFailedAndTimeoutCommands($lockTimeout = false)
    {
        // Fist, get all failed commands (return != 0)
        $failedCommands = $this->findFailedCommand();

        // Then, si a timeout value is set, get locked commands and check timeout
        if (false !== $lockTimeout) {
            $lockedCommands = $this->findLockedCommand();
            foreach ($lockedCommands as $lockedCommand) {
                $now = time();
                if ($lockedCommand->getLastExecution()->getTimestamp() + $lockTimeout < $now) {
                    $failedCommands[] = $lockedCommand;
                }
            }
        }

        return $failedCommands;
    }

    /**
     * @param ScheduledCommand $command
     * @return mixed
     * @throws NonUniqueResultException
     * @throws TransactionRequiredException
     */
    public function getNotLockedCommand(ScheduledCommand $command)
    {
        $query = $this->createQueryBuilder('command')
            ->where('command.locked = false')
            ->andWhere('command.id = :id')
            ->setParameter('id', $command->getId())
            ->getQuery();

        $query->setLockMode(LockMode::PESSIMISTIC_WRITE);

        return $query->getOneOrNullResult();
    }
}
