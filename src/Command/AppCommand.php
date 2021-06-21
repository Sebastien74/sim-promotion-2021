<?php

namespace App\Command;

use App\Service\Development\CommandHelper;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * AppCommand
 *
 * Helper to run CMS commands
 *
 * @property string $defaultName
 * @property CommandHelper $commandHelper
 * @property array $commands
 * @property SymfonyStyle $io
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class AppCommand extends Command
{
    protected static $defaultName = 'app:cmd';

    private $commandHelper;
    private $io;
    private $commands;

    /**
     * AppCommand constructor.
     *
     * @param CommandHelper $commandHelper
     */
    public function __construct(CommandHelper $commandHelper)
    {
        $this->commandHelper = $commandHelper;

        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setDescription('Helper to run CMS commands.')
            ->addArgument('alias', InputArgument::OPTIONAL, 'Alias of command you want to run.');
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        if (!$this->commandHelper->isAllowed($this->io)) {
            return 0;
        }

        $this->commands = $this->commandHelper->setCommands();

        if ($this->runAlias($input, $output)) {
            return 0;
        }

        $this->runHelper($output);

        return 0;
    }

    /**
     * Run alias command
     *
     * @param InputInterface $input
     * @return bool
     * @throws Exception
     */
    private function runAlias(InputInterface $input, OutputInterface $output): bool
    {
        $alias = $input->getArgument('alias');

        if (!empty($this->commands[$alias]['command']['website'])) {
            $this->io->error("You must run this command whit Helper!!!" . $alias);
            return true;
        } elseif ($alias && !empty($this->commands[$alias]['command'])) {
            $this->commandHelper->runCmd($alias, $this->io, $output);
            return true;
        } elseif ($alias) {
            $this->io->error("This alias doesn't exist!!!");
            return true;
        }

        return false;
    }

    /**
     * Run commands Helper
     *
     * @param OutputInterface $output
     * @throws Exception
     */
    private function runHelper(OutputInterface $output)
    {
        $this->io->title('Welcome to CMS run commands Helper!!!');

        $this->setList();
        $alias = $this->setChoices();

        if (!empty($this->commands[$alias]['command']['website'])) {
            $website = $this->commandHelper->getWebsites($this->io);
            $this->commandHelper->setCommandArgument($alias, 'website', $website);
        }

        if (!empty($this->commands[$alias]['command']['symfony_style'])) {
            $this->commandHelper->setCommandArgument($alias, 'symfony_style', $this->io);
        }

        if (!empty($this->commands[$alias]['command']['output'])) {
            $this->commandHelper->setCommandArgument($alias, 'output', $output);
        }

        $this->commandHelper->runCmd($alias, $this->io, $output);
    }

    /**
     * Set list of commands helper
     */
    private function setList(): void
    {
        $items = [];
        foreach ($this->commands as $alias => $configuration) {
            $items[] = [$alias, $this->commands[$alias]['command']['command'], $configuration['description']];
        }

        $this->io->table(['Alias', 'Command run', 'Description'], $items);
    }

    /**
     * Set choices select of commands
     *
     * @return string
     */
    private function setChoices(): string
    {
        $choices = [];
        foreach ($this->commands as $alias => $configuration) {
            $choices[] = $alias;
        }

        return $this->io->choice('Select command', $choices);
    }
}