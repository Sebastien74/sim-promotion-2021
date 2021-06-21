<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function url()
{
    if (isset($_SERVER['HTTPS'])) {
        $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
    } else {
        $protocol = 'http';
    }
    return $protocol . "://" . $_SERVER['HTTP_HOST'];
}

// DANS INDEX
//        $rootDir = str_replace("\public", "", dirname(__DIR__));
//        executeAsyncShellCommand($rootDir, "php composer.phar install"); 
/**
 * Executes a console command (like execute the command via putty) in
 * a linux environment or windows from php without await for the result.
 *
 * Useful for execute extense tasks.
 *
 * @param {String} $comando
 */
function executeAsyncShellCommand($dir, $comando = null)
{
//    if(!$comando) {
//        throw new \Exception("No command given");
//    }
//    
//    // If windows, else
//    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
//        system("cd ".$dir." & ".$comando." > NUL");
//    }
//    else {
//        shell_exec("cd ".$dir." & ".$comando." >/dev/null 2>&1 &");
//    }
}