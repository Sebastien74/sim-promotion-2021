<?php

namespace App\Command;

use App\Service\Security\PasswordExpire;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * SecurityPasswordExpireCommand
 *
 * Check if users passwords expire and send email
 *
 * @property string $defaultName
 * @property PasswordExpire $passwordExpire
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SecurityPasswordExpireCommand extends Command
{
    protected static $defaultName = 'security:password:expire';

    private $passwordExpire;

    /**
     * SecurityPasswordExpireCommand constructor.
     *
     * @param PasswordExpire $passwordExpire
     */
    public function __construct(PasswordExpire $passwordExpire)
    {
        $this->passwordExpire = $passwordExpire;

        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setDescription('Check if users passwords expire and send email.')
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
        $this->passwordExpire->execute($input, self::$defaultName);

        return 0;
    }
}