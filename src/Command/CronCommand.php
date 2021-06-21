<?php

namespace App\Command;

use App\Service\Core\CronSchedulerService;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CronCommand
 *
 * Run all commands scheduled
 *
 * @property string $defaultName
 * @property CronSchedulerService $cronSchedulerService
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CronCommand extends Command
{
    protected static $defaultName = 'scheduler:execute';

    private $cronSchedulerService;

    /**
     * CronCommand constructor.
     *
     * @param CronSchedulerService $cronSchedulerService
     */
    public function __construct(CronSchedulerService $cronSchedulerService)
    {
        $this->cronSchedulerService = $cronSchedulerService;

        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setDescription('Execute scheduled commands')
            ->setHelp('This class is the entry point to execute all scheduled command');
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->cronSchedulerService->execute($input, $output);

        return 0;
    }
}