<?php

error_reporting(E_ALL & ~E_NOTICE & ~E_USER_NOTICE & ~E_DEPRECATED & ~E_USER_DEPRECATED
    & ~E_WARNING & ~E_CORE_WARNING & ~E_USER_WARNING & ~E_STRICT);

//var_dump(dirname(__DIR__) . '/composer.json');
$composer_data = array(
//    'url' => 'https://getcomposer.org/composer.phar',
//    'dir' => dirname(__DIR__).'/.code',
    'dir' => __DIR__,
//    'bin' => dirname(__DIR__).'/.code/composer.phar',
    'json' => __DIR__ . '/composer.json',
    'conf' => json_decode(file_get_contents(__DIR__ . '/composer.json'))
);
//
//mkdir($composer_data['dir'],0777,true);
//mkdir("{$composer_data['dir']}/local",0777,true);
//copy($composer_data['url'], $composer_data['bin']);
//require_once "phar://{$composer_data['bin']}/src/bootstrap.php";
require_once "phar://" . __DIR__ . "/composer.phar/src/bootstrap.php";

//$conf_json = json_encode($composer_data['conf'],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
//$conf_json = file_get_contents(dirname__DIR__ . '/composer.json');
//file_put_contents($composer_data['json'], $conf_json);
//chdir($composer_data['dir']);

var_dump($composer_data['dir']);

putenv("COMPOSER_HOME={$composer_data['dir']}");
putenv("OSTYPE=OS400"); //force to use php://output instead of php://stdout
$app = new \Composer\Console\Application();

$factory = new \Composer\Factory();
$output = $factory->createOutput();

$input = new \Symfony\Component\Console\Input\ArrayInput(array(
    'command' => 'dump-autoload',
    '--no-dev' => true,
    '--classmap-authoritative' => true,
));
$input->setInteractive(false);
echo "<pre>";
$cmdret = $app->doRun($input,$output); //unfortunately ->run() call exit() so we use doRun()



//require dirname(__DIR__) . '/composer.phar/vendor/autoload.php';

//use Symfony\Component\Filesystem\Filesystem;
//use Composer\Console\Application;
//use Symfony\Component\Console\Input\ArrayInput;
//
//require dirname(__DIR__) . '/config/bootstrap.php';

///**
// * Execute composer.phar dump-autoload --no-dev --classmap-authoritative
// *
// * @return false|string|null
// * @throws Exception
// */
//function executeAsyncShellCommand()
//{
//    putenv('COMPOSER_HOME=' . __DIR__ . '/composer.phar');
//
//    $input = new ArrayInput(array('command' => 'dump-autoload', '--no-dev' => true, '--classmap-authoritative' => true));
//    $application = new Application();
//    $application->setAutoExit(false); // prevent `$application->run` method from exitting the script
//    $application->run($input);

//    $php = 'php';
//    if($_ENV['SERVER_HOST'] === 'o2s') {
//        $php = '/opt/alt/' . $_ENV['SERVER_HOST_PHP_VERSION'] . '/usr/bin/php';
//    }
//    elseif($_ENV['SERVER_HOST'] === 'ovh') {
//        $php = '/usr/local/' . $_ENV['SERVER_HOST_PHP_VERSION'] . '/bin/php';
//    }
//
//    $composerDirname = dirname(__DIR__) . '\composer.phar';
//    $cmd = 'dump-autoload';
//
//    $process = new Process([$php . ' ' . $composerDirname . ' ' . $cmd, '--no-dev', '--classmap-authoritative']);
//    $process->run();
//
//    // executes after the command finishes
//    if (!$process->isSuccessful()) {
//        echo new ProcessFailedException($process);
//    }
//
//    echo $process->getOutput();

//    $output = 'Execution failed!!';
//    $php = 'php';
//    $cmd = 'dump-autoload --no-dev --classmap-authoritative';
//    $composerDirname = dirname(__DIR__) . '\composer.phar';
//    $asynchronous = filter_var($_ENV['SERVER_CMD_ASYNCHRONOUS'], FILTER_VALIDATE_BOOLEAN);
//
//    if($_ENV['SERVER_HOST'] === 'o2s') {
//        $php = '/opt/alt/' . $_ENV['SERVER_HOST_PHP_VERSION'] . '/usr/bin/php';
//    }
//    elseif($_ENV['SERVER_HOST'] === 'ovh') {
//        $php = '/usr/local/' . $_ENV['SERVER_HOST_PHP_VERSION'] . '/bin/php';
//    }
//
//    $filesystem = new Filesystem();
//
//    if($php && $filesystem->exists($composerDirname) && $filesystem->exists($php) || $_ENV['SERVER_HOST'] === 'local' && $php === 'php') {
//
//        // If windows, else
//        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && $asynchronous) {
//            $output = system($php . ' ' . $composerDirname . ' ' . $cmd . ' > NUL');
//        }
//        elseif (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && !$asynchronous) {
//            $output = system($php . ' ' . $composerDirname . ' ' . $cmd);
//        }
//        elseif($asynchronous) {
//            $output = shell_exec($php . ' ' . $composerDirname . ' ' . $cmd . ' >/dev/null');
//        }
//        elseif(!$asynchronous) {
//            $output = shell_exec($php . ' ' . $composerDirname . ' ' . $cmd);
//        }
//    }
//
//    return $output;
//}

//try {
//
//    if(!empty($_SERVER['HTTP_REFERER']) && preg_match('/\/admin-' . $_ENV['SECURITY_TOKEN'] . '/', $_SERVER['HTTP_REFERER'])) {
//        $output = executeAsyncShellCommand();
////        header('Location: ' . $_SERVER['HTTP_REFERER']);
//    }
//    else {
//        include 'denied.php';
//        exit();
//    }
//}
//catch (\Exception $exception) {}