<?php

namespace App\Service\Development;

use App\Entity\Core\Website;
use App\Repository\Core\WebsiteRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * CommandHelper
 *
 * Symfony style website selector for command
 *
 * @property WebsiteRepository $websiteRepository
 * @property KernelInterface $kernel
 * @property SymfonyStyle $io
 * @property array $commands
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CommandHelper
{
    private $websiteRepository;
    private $kernel;
    private $io;
    private $commands;

    /**
     * CommandHelper constructor.
     *
     * @param WebsiteRepository $websiteRepository
     * @param KernelInterface $kernel
     */
    public function __construct(WebsiteRepository $websiteRepository, KernelInterface $kernel)
    {
        $this->websiteRepository = $websiteRepository;
        $this->kernel = $kernel;
    }

    /**
     * Check if user is allowed to execute command
     *
     * @param SymfonyStyle $io
     * @return bool
     */
    public function isAllowed(SymfonyStyle $io)
    {
        $this->io = $io;

        if ($_ENV['APP_ENV_NAME'] !== 'local') {
            $this->io->error("You're not allowed to run this command!!!");
            return false;
        }

        return true;
    }

    /**
     * Set App command
     *
     * @return array
     */
    public function setCommands(): array
    {
        $this->commands['update'] = ['command' => [
            'command' => 'doctrine:schema:update',
            '--force' => true
        ],
            'description' => 'Executes (or dumps) the SQL needed to update the database schema to match the current mapping metadata',
            'start' => 'Updating in progress...', 'finish' => "You're entities are successfully updated."
        ];

        $this->commands['entities'] = ['command' => [
            'command' => 'app:cmd:entities',
            'website' => true,
            'symfony_style' => true,
            'output' => true,
        ], 'description' => 'To generate entities configurations'];

        $this->commands['translations'] = ['command' => [
            'command' => 'app:cmd:translations',
            'website' => true,
            'symfony_style' => true,
            'output' => true,
        ], 'description' => 'To generate translations'];

        return $this->commands;
    }

    /**
     * Sezt command argument
     *
     * @param string $alias
     * @param string $argument
     * @param $value
     */
    public function setCommandArgument(string $alias, string $argument, $value): void
    {
        $this->commands[$alias]['command'][$argument] = $value;
    }

    /**
     * Generate selector
     *
     * @param SymfonyStyle $io
     * @return Website|null
     */
    public function getWebsites(SymfonyStyle $io)
    {
        $websites = $this->websiteRepository->findAll();
        $websitesSlugs = [];

        foreach ($websites as $website) {
            $websitesSlugs[] = $website->getSlug();
        }

        if ($websitesSlugs) {
            $websiteCode = $io->choice('Choose Website code', $websitesSlugs);
            return $this->websiteRepository->findOneBy(['slug' => $websiteCode]);
        }

        $io->getErrorStyle()->warning('No Website found!');
    }

    /**
     * Get Website
     *
     * @param InputInterface $input
     * @param SymfonyStyle $io
     * @return Website
     */
    public function getWebsite(InputInterface $input, SymfonyStyle $io)
    {
        $websiteArgument = $input->getArgument('website');
        $website = is_string($websiteArgument) ? $this->websiteRepository->findOneBy(['slug' => $websiteArgument]) : NULL;
        if($website instanceof Website) {
            return $website;
        }

        return $websiteArgument instanceof Website ? $websiteArgument : $this->getWebsites($io);
    }

    /**
     * Get SymfonyStyle
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return string|string[]|SymfonyStyle
     */
    public function getIo(InputInterface $input, OutputInterface $output)
    {
        return $input->getArgument('symfony_style')
            ? $input->getArgument('symfony_style') : new SymfonyStyle($input, $output);
    }

    /**
     * Get SymfonyStyle
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return string|string[]|OutputInterface|SymfonyStyle
     */
    public function getOutput(InputInterface $input, OutputInterface $output)
    {
        return $input->getArgument('output') ? $input->getArgument('output') : $output;
    }

    /**
     * Run command
     *
     * @param string $alias
     * @param SymfonyStyle $io
     * @param OutputInterface $output
     * @throws Exception
     */
    public function runCmd(string $alias, SymfonyStyle $io, OutputInterface $output): void
    {
        $command = $this->commands[$alias]['command'];

        if (!empty($this->commands[$alias]['start'])) {
            $output->writeln('<comment>' . $this->commands[$alias]['start'] . '</comment>');
            $io->newLine();
        }

        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput($command);
        $output = new BufferedOutput();
        $application->run($input, $output);

        if (!empty($this->commands[$alias]['finish'])) {
            $io->block($this->commands[$alias]['finish'], 'OK', 'fg=black;bg=green', ' ', true);
        }
    }
}