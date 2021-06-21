<?php

namespace App\Command;

use App\Repository\Core\ScheduledCommandRepository;
use App\Service\Core\GdprService;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * GdprCommand
 *
 * To run GDPR remove data commands
 *
 * @property string $defaultName
 * @property GdprService $gdprService
 * @property ScheduledCommandRepository $commandRepository
 * @property SymfonyStyle $io
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class GdprCommand extends Command
{
    protected static $defaultName = 'gdpr:remove';

    private $gdprService;
    private $commandRepository;
    private $io;

    /**
     * GdprCommand constructor.
     *
     * @param GdprService $gdprService
     * @param ScheduledCommandRepository $commandRepository
     */
    public function __construct(GdprService $gdprService, ScheduledCommandRepository $commandRepository)
    {
        $this->gdprService = $gdprService;
        $this->commandRepository = $commandRepository;

        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setDescription('GDPR remove data.')
            ->addArgument('cronLogger', InputArgument::OPTIONAL, 'Cron scheduler Logger')
            ->addArgument('commandLogger', InputArgument::OPTIONAL, 'Command Logger');
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): string
    {
        $this->io = new SymfonyStyle($input, $output);

        $commands = $this->commandRepository->findBy(['command' => self::$defaultName]);

        foreach ($commands as $command) {
            $this->gdprService->removeData($command->getWebsite(), $input, self::$defaultName);
        }

        $message = 'GDPR data successfully deleted.';
        $this->io->block($message, 'OK', 'fg=black;bg=green', ' ', true);

        return $message;
    }
}