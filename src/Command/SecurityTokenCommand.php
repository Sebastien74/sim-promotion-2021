<?php

namespace App\Command;

use App\Entity\Security\User;
use App\Entity\Security\UserFront;
use App\Service\Core\CronSchedulerService;
use DateInterval;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * SecurityTokenCommand
 *
 * To set user reset password token on NULL after 24H
 *
 * @property string $defaultName
 * @property EntityManagerInterface $entityManager
 * @property CronSchedulerService $cronSchedulerService
 * @property SymfonyStyle $io
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SecurityTokenCommand extends Command
{
    protected static $defaultName = 'security:reset:token';

    private $entityManager;
    private $cronSchedulerService;

    /**
     * SecurityTokenCommand constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param CronSchedulerService $cronSchedulerService
     */
    public function __construct(EntityManagerInterface $entityManager, CronSchedulerService $cronSchedulerService)
    {
        $this->entityManager = $entityManager;
        $this->cronSchedulerService = $cronSchedulerService;

        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setDescription('To set user reset password token on NULL after 24H.')
            ->addArgument('cronLogger', InputArgument::OPTIONAL, 'Cron scheduler Logger')
            ->addArgument('commandLogger', InputArgument::OPTIONAL, 'Command Logger');
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->checkTokens(User::class, $input);
        $this->checkTokens(UserFront::class, $input);

        $this->cronSchedulerService->logger('[EXECUTED] ' . self::$defaultName, $input);

        return 0;
    }

    /**
     * Check & set token
     *
     * @param string $classname
     * @param InputInterface $input
     * @throws Exception
     */
    private function checkTokens(string $classname, InputInterface $input)
    {
        $users = $this->getUsers($classname);

        foreach ($users as $user) {

            /** @var User|UserFront $user */

            $now = new DateTime('now', new DateTimeZone('Europe/Paris'));

            $date = $user->getTokenRequest();
            $date = new DateTime($date->format('Y-m-d H:i:s'), new DateTimeZone('Europe/Paris'));
            $date->add(new DateInterval('PT2H'));

            if ($now > $date) {
                $user->setToken(NULL);
                $this->entityManager->persist($user);
                $this->entityManager->flush();
            }
        }

        $message = '[OK] ' . $classname . ' tokens successfully reset.';
        $this->io->block($message, 'OK', 'fg=black;bg=green', ' ', true);
        $this->cronSchedulerService->logger($message, $input);
    }

    /**
     * Get Users with token
     *
     * @param string $classname
     * @return array
     */
    private function getUsers(string $classname)
    {
        return $this->entityManager->createQueryBuilder()->select('u')
            ->from($classname, 'u')
            ->andWhere('u.token IS NOT NULL')
            ->andWhere('u.tokenRequest IS NOT NULL')
            ->getQuery()
            ->getResult();
    }
}