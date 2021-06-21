<?php

namespace App\Command;

use App\Entity\Core\Website;
use App\Service\Development\CommandHelper;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * InitCommand
 *
 * CMS init project command
 *
 * @property string $defaultName
 * @property CommandHelper $commandHelper
 * @property SymfonyStyle $io
 * @property EntityManagerInterface $entityManager
 * @property DoctrineCommand $doctrineCommand
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class InitCommand extends Command
{
    protected static $defaultName = 'app:cms:init';

    private $commandHelper;
    private $doctrineCommand;
    private $io;

    /**
     * AppCommand constructor.
     *
     * @param CommandHelper $commandHelper
     * @param EntityManagerInterface $entityManager
     * @param DoctrineCommand $doctrineCommand
     */
    public function __construct(
        CommandHelper $commandHelper,
        EntityManagerInterface $entityManager,
        DoctrineCommand $doctrineCommand)
    {
        $this->commandHelper = $commandHelper;
        $this->entityManager = $entityManager;
        $this->doctrineCommand = $doctrineCommand;

        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setDescription('CMS Project initialisation.')
            ->addArgument('alias', InputArgument::OPTIONAL, 'To vreate new project.');
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

        try {

            $websites = $this->entityManager->getRepository(Website::class)->findAll();

            if ($websites) {
                $this->io->error("Project already initialize!!");
                return 0;
            }
        } catch (\Exception $exception) {

            $output->writeln('<comment>Updating database...</comment>');
            $this->doctrineCommand->update();
            $output->writeln('<info>Database successfully updated!!!</info>');

            $output->writeln('<comment>Fixtures loading...</comment>');
            $this->doctrineCommand->fixtures();
            $output->writeln('<info>Fixtures successfully loaded!!!</info>');
        }

        return 0;
    }
}