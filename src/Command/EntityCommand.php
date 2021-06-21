<?php

namespace App\Command;

use App\Service\Development\EntityService;
use App\Service\Development\CommandHelper;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * AppCommand
 *
 * Helper to run CMS commands
 *
 * @property string $defaultName
 * @property EntityService $entityService
 * @property CommandHelper $commandHelper
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class EntityCommand extends Command
{
    protected static $defaultName = 'app:cmd:entities';

    private $entityService;
    private $commandHelper;

    /**
     * AppCommand constructor.
     *
     * @param EntityService $entityService
     * @param CommandHelper $commandHelper
     */
    public function __construct(EntityService $entityService, CommandHelper $commandHelper)
    {
        $this->entityService = $entityService;
        $this->commandHelper = $commandHelper;

        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setDescription('To regenerate entities configuration.')
            ->addArgument('website', InputArgument::OPTIONAL, 'Website entity.')
            ->addArgument('symfony_style', InputArgument::OPTIONAL, 'SymfonyStyle entity.')
            ->addArgument('output', InputArgument::OPTIONAL, 'Command output.');
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = $this->commandHelper->getIo($input, $output);

        if (!$this->commandHelper->isAllowed($io)) {
            return 0;
        }

        $output = $this->commandHelper->getOutput($input, $output);
        $website = $this->commandHelper->getWebsite($input, $io);

        if ($website) {
            $output->writeln('<comment>Regeneration progressing...</comment>');
            $io->newLine();
            $this->entityService->execute($website, $website->getConfiguration()->getLocale());
            $io->block('Entities successfully regenerated.', 'RUN', 'fg=black;bg=green', ' ', true);
        }

        return 0;
    }
}