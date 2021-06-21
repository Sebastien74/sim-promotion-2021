<?php

namespace App\Service\Core;

use App\Command\CronCommand;
use App\Entity\Core\ScheduledCommand;
use Cron\CronExpression;
use DateTime;
use Doctrine\DBAL\ConnectionException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Gedmo\Sluggable\Util\Urlizer;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * CronSchedulerService
 *
 * Run all commands scheduled
 *
 * @property string $defaultName
 * @property EntityManagerInterface $entityManager
 * @property KernelInterface $kernel
 * @property string $logPath
 * @property Logger $logger
 * @property string $message
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CronSchedulerService
{
    private $entityManager;
    private $kernel;
    private $logPath;
    private $logger;

    /**
     * CronCommand constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param KernelInterface $kernel
     */
    public function __construct(EntityManagerInterface $entityManager, KernelInterface $kernel)
    {
        $this->entityManager = $entityManager;
        $this->kernel = $kernel;

        $this->logPath = $kernel->getLogDir();
        if (false !== $this->logPath) {
            $this->logPath = rtrim($this->logPath, '/\\') . DIRECTORY_SEPARATOR;
        }

        $this->logger = new Logger('CRON');
        $this->logger->pushHandler(new RotatingFileHandler($this->logPath . 'cron-scheduler.log', 20, Logger::INFO));
    }

    /**
     * Execute
     *
     * @param InputInterface|null $input
     * @param OutputInterface|null $output
     * @throws Exception
     */
    public function execute(InputInterface $input = NULL, OutputInterface $output = NULL)
    {
        $this->logger->info('[START] ' . CronCommand::class);
        $this->logger->info('[START] ' . CronSchedulerService::class);

        $noneExecution = true;
        $commands = $this->entityManager->getRepository(ScheduledCommand::class)->findAll();

        foreach ($commands as $command) {

            /** @var ScheduledCommand $command */

            $this->entityManager->refresh($command);

            $message = NULL;
            $logFilename = $this->getFilename($command);
            $commandLogger = new Logger('CRON');
            $commandLogger->pushHandler(new RotatingFileHandler($this->logPath . $logFilename, 10, Logger::INFO));

            if (!$this->isExecutable($command, $commandLogger)) {
                continue;
            }

            if ($command->isExecuteImmediately()) {
                $noneExecution = false;
                $this->executeCommand($command, $commandLogger, $logFilename);
            } else {

                try {

                    $cron = CronExpression::factory($command->getCronExpression());
                    $nextRunDate = $cron->getNextRunDate($command->getLastExecution());

                    if ($nextRunDate < new DateTime()) {
                        $noneExecution = false;
                        $this->executeCommand($command, $commandLogger, $logFilename);
                    }

                } catch (Exception $exception) {
                    $this->logger->critical('[ERROR] - ' . $command->getCommand() . ' (' . $exception->getMessage() . ')');
                    $commandLogger->critical('[ERROR] - ' . $command->getCommand() . ' (' . $exception->getMessage() . ')');
                    continue;
                }
            }
        }

        if (true === $noneExecution) {
            $this->logger->info('[CLOSE] Nothing to do.');
        }

        $this->logger->info('[CLOSE] ' . CronCommand::class . ' executed.');
    }

    /**
     * Logger
     *
     * @param string $message
     * @param InputInterface|null $input
     */
    public function logger(string $message, InputInterface $input = NULL)
    {
        if ($input) {

            $cronLogger = $input->getArgument('cronLogger');
            if ($cronLogger) {
                $logger = new Logger('CRON');
                $logger->pushHandler(new RotatingFileHandler($this->logPath . $cronLogger, 10, Logger::INFO));
                $logger->info($message);
            }

            $commandLogger = $input->getArgument('commandLogger');
            if ($commandLogger) {
                $logger = new Logger('CRON');
                $logger->pushHandler(new RotatingFileHandler($this->logPath . $commandLogger, 10, Logger::INFO));
                $logger->info($message);
            }
        }
    }

    /**
     * Get log filename
     *
     * @param ScheduledCommand $command
     * @return string
     */
    private function getFilename(ScheduledCommand $command)
    {
        $filename = !preg_match('/.log/', $command->getLogFile()) ? $command->getLogFile() . '.log' : $command->getLogFile();

        if (!$filename || $filename === '.log') {
            $filename = 'cron-' . Urlizer::urlize($command->getCommand());
        }

        return $filename . '.log';
    }

    /**
     * Check if command is executable
     *
     * @param ScheduledCommand $command
     * @param Logger $commandLogger
     * @return string
     */
    private function isExecutable(ScheduledCommand $command, Logger $commandLogger)
    {
        if (!$command->isActive() || $command->isLocked()) {
            $cmdMessage = $command->isLocked() ? '[LOCKED] ' . $command->getCommand() . ' is locked' : '[DISABLED] ' . $command->getCommand() . ' is disabled';
            $this->logger->warning($cmdMessage);
            $commandLogger->warning($cmdMessage);
            return false;
        }

        return true;
    }

    /**
     * Execute command
     *
     * @param ScheduledCommand $scheduledCommand
     * @param Logger $commandLogger
     * @param string $logFilename
     * @return string
     * @throws ConnectionException
     * @throws Exception
     */
    private function executeCommand(ScheduledCommand $scheduledCommand, Logger $commandLogger, string $logFilename)
    {
        $this->entityManager->getConnection()->beginTransaction();

        try {

            $notLockedCommand = $this->entityManager->getRepository(ScheduledCommand::class)->getNotLockedCommand($scheduledCommand);
            if ($notLockedCommand === null) {
                throw new Exception();
            }

            $scheduledCommand = $notLockedCommand;
            $scheduledCommand->setLastExecution(new DateTime());
            $scheduledCommand->setLocked(true);

            $this->entityManager->persist($scheduledCommand);
            $this->entityManager->flush();
            $this->entityManager->getConnection()->commit();
        } catch (Exception $exception) {
            $this->entityManager->getConnection()->rollBack();
            $this->logger->critical($exception->getMessage());
            return 0;
        }

        $this->logger->info('[START] ' . $scheduledCommand->getCommand());
        $commandLogger->info('[START] ' . $scheduledCommand->getCommand());

        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => $scheduledCommand->getCommand(),
            'cronLogger' => 'cron-scheduler.log',
            'commandLogger' => $logFilename
        ]);
        $output = new BufferedOutput();
        $application->run($input, $output);

        if (!$scheduledCommand->isExecuteImmediately()) {
            $scheduledCommand->setLastExecution(new DateTime());
        }

        $scheduledCommand->setExecuteImmediately(false);
        $scheduledCommand->setLocked(false);
        $this->entityManager->persist($scheduledCommand);
        $this->entityManager->flush();
        $this->entityManager->refresh($scheduledCommand);

        $this->logger->info('[SUCCESS] ' . $scheduledCommand->getCommand());
        $commandLogger->info('[SUCCESS] ' . $scheduledCommand->getCommand());

        return trim($output->fetch()) . ' - ';
    }
}