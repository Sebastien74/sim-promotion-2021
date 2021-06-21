<?php

use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\PhpExecutableFinder;

require dirname(__DIR__) . '/config/bootstrap.php';

$asynchronous = true;

/**
 * CronScheduler
 *
 * To run Cron Scheduler
 * Recommended run method execute(). If not working run executeProcedural()
 *
 * @property string $cmd
 * @property string $environment
 * @property bool $asynchronous
 * @property string $dirname
 * @property string $phpExecutable
 * @property Logger $logger
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CronScheduler
{

    private static $cmd = 'scheduler:execute';
    private $environment;
    private $asynchronous = false;
    private $dirname;
    private $phpExecutable = 'php';
    private $logger;

    /**
     * Cron constructor.
     *
     * @param Logger $logger
     * @param bool $asynchronous
     */
    public function __construct(Logger $logger, bool $asynchronous = false)
    {
        $this->dirname = dirname(__DIR__);
        $this->environment = $_ENV['APP_ENV_NAME'];
        $this->asynchronous = $asynchronous;

        $this->logger = $logger;
        $this->logger->info('================== Start ==================');
        $this->logger->info('Environment: ' . $this->environment);
        $this->logger->info('Asynchronous: ' . $this->asynchronous);
    }

    /**
     * Run command : Procedural PHP execution
     *
     * @throws Exception
     */
    public function executeProcedural()
    {
        $this->logger->info('Executed command method : executeProcedural()');

        $this->setPHPExecutable();
        $this->executeShellCommand();
    }

    /**
     * Get php executable
     *
     * @throws Exception
     */
    private function setPHPExecutable(): void
    {
        $phpPath = $this->phpExecutable;

        if ($this->environment !== 'local') {
            $phpFinder = new PhpExecutableFinder;
            if (!$phpPath = $phpFinder->find()) {
                $this->logger->critical('The php executable could not be found');
                throw new Exception('The php executable could not be found, add it to your PATH environment variable and try again');
            }
        }

        $this->phpExecutable = $phpPath;
        $this->logger->info('PHP executable : ' . $phpPath);
    }

    /**
     * Executes Shell command.
     *
     * @return false|string|null
     */
    private function executeShellCommand()
    {
        $filesystem = new Filesystem();
        $output = 'Execution failed!!';
        $executableCmd = NULL;
        $consoleDirname = $this->dirname . '/bin/console';
        $asynchronous = filter_var($this->asynchronous, FILTER_VALIDATE_BOOLEAN);

        if ($this->phpExecutable && $filesystem->exists($consoleDirname) && $filesystem->exists($this->phpExecutable)
            || $this->environment === 'local' && $this->phpExecutable === 'php') {

            /** If windows, else */
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && $asynchronous) {
                $executableCmd = $this->phpExecutable . ' ' . $consoleDirname . ' ' . self::$cmd . " > NUL";
                $this->logger->info('Executable command: ' . $executableCmd);
                $output = system($executableCmd);
            } elseif (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && !$asynchronous) {
                $executableCmd = $this->phpExecutable . ' ' . $consoleDirname . ' ' . self::$cmd;
                $this->logger->info('Executable command: ' . $executableCmd);
                $output = system($executableCmd);
            } elseif ($asynchronous) {
                $executableCmd = $this->phpExecutable . ' ' . $consoleDirname . ' ' . self::$cmd . ' >/dev/null';
                $this->logger->info('Executable command: ' . $executableCmd);
                $output = shell_exec($executableCmd);
            } elseif (!$asynchronous) {
                $executableCmd = $this->phpExecutable . ' ' . $consoleDirname . ' ' . self::$cmd;
                $this->logger->info('Executable command: ' . $executableCmd);
                $output = shell_exec($executableCmd);
            }
        }

        $this->logger->info('Cron OUTPUT : ' . $output);
        $this->logger->info('END execution');

        return $output;
    }
}

$logger = new Logger('CRON');
$logger->pushHandler(new RotatingFileHandler(dirname(__DIR__) . '/var/log/cron-scheduler.log', 20, Logger::INFO));

$scheduler = new CronScheduler($logger, $asynchronous);

try {
    $scheduler->executeProcedural();
    echo json_encode([
        'result' => 'Cron successfully executed.'
    ], true);
} catch (Exception $exception) {
    $logger->critical($exception->getMessage());
    echo json_encode([
        'result' => $exception->getMessage()
    ], true);
}

header('Content-Type: application/json');

exit();