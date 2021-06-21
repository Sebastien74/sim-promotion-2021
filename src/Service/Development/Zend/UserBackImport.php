<?php

namespace App\Service\Development\Zend;

use App\Entity\Core\Website;
use App\Entity\Security\Group;
use App\Entity\Security\User;
use App\Service\Development\SqlService;
use DateTime;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * UserBackImport
 *
 * @property EntityManagerInterface $entityManager
 * @property SqlService $sqlService
 * @property KernelInterface $kernel
 * @property SymfonyStyle $io
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class UserBackImport
{
    private $entityManager;
    private $sqlService;
    private $kernel;
    private $io;

    /**
     * UserBackImport constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param SqlService $sqlService
     * @param KernelInterface $kernel
     */
    public function __construct(EntityManagerInterface $entityManager, SqlService $sqlService, KernelInterface $kernel)
    {
        $this->entityManager = $entityManager;
        $this->sqlService = $sqlService;
        $this->kernel = $kernel;
    }

    /**
     * Import Users Back
     *
     * @param Website $website
     * @param Group|null $group
     * @param SymfonyStyle $io
     * @throws DBALException
     */
    public function import(Website $website, Group $group = NULL, SymfonyStyle $io = NULL)
    {
        $this->io = $io;

        $usersBack = $this->sqlService->findAll('fxc_users_back');
        $repository = $this->entityManager->getRepository(User::class);
        $position = count($repository->findAll()) + 1;
        $locale = $website->getConfiguration()->getLocale();
        $group = $group ? $group : $this->entityManager->getRepository(Group::class)->findOneBy(['slug' => 'administrator']);

        if ($this->io && count($usersBack) > 0) {
            $this->io->write('<comment>Users Back extraction progressing...</comment>');
            $this->io->newLine();
            $this->io->progressStart(count($usersBack));
        } else {
            $this->io->write('<comment>No Users admin were found.</comment>');
        }

        foreach ($usersBack as $userBack) {

            $userBack = (object)$userBack;

            /** @var User $existing */
            $existing = $repository->findExisting($userBack->user_login, $userBack->user_email);

            if (!$existing) {

                $user = new User();
                $user->setLogin($userBack->user_login);
                $user->setEmail($userBack->user_email);
                $user->setFirstName($userBack->user_first_name);
                $user->setLastName($userBack->user_last_name);
                $user->setPassword($userBack->user_password);
                $user->setAgreesTermsAt(new DateTime('now'));
                $user->setLocale($locale);
                $user->setResetPassword(true);
                $user->setGroup($group);

                $this->entityManager->persist($user);
                $this->entityManager->flush();
                $position++;
            } else {
                $logger = new Logger('EXISTING_USER_BACK');
                $logger->pushHandler(new RotatingFileHandler($this->kernel->getLogDir() . '/zend-import.log', 10, Logger::WARNING));
                $logger->warning('Login :' . $userBack->user_login . ' Email :' . $userBack->user_email);
            }

            if ($this->io) {
                $this->io->progressAdvance();
            }
        }

        if ($this->io && count($usersBack) > 0) {
            $this->io->progressFinish();
            $this->io->write('Users Back successfully extracted.');
        }

        if ($this->io) {
            $this->io->newLine(2);
        }
    }
}