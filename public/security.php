<?php

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

session_start();

require dirname(__DIR__) . '/config/bootstrap.php';

$matches = explode('/', $_SERVER['REQUEST_URI']);
$secureKey = $matches[count($matches) - 2];

$validToken = !empty($_GET['token']) && $_GET['token'] === $_ENV['APP_SECRET'];

$isAdminFile = preg_match('/build\/admin/', $_SERVER['REQUEST_URI'])
    && !empty($_GET['isAdmin']) && $_GET['isAdmin']
    && !empty($_GET['userSecret'])
    && !empty($_GET['token'])
    && $validToken
    || preg_match('/build\/admin/', $_SERVER['REQUEST_URI'])
    && !empty($_SESSION['SECURITY_IS_ADMIN'])
    && $_SESSION['SECURITY_IS_ADMIN']
    && !empty($_SESSION['SECURITY_USER_SECRET']);

$isValid = false;
if ($isAdminFile) {
    $isValid = true;
} elseif (preg_match('/uploads\/emails/', $_SERVER['REQUEST_URI']) && $validToken) {
    $isValid = true;
} elseif (isset($_SESSION['SECURITY_USER_SECRET']) && $_SESSION['SECURITY_USER_SECRET'] === $secureKey) {
    $isValid = true;
}

$pathMatches = explode('?', $_SERVER['DOCUMENT_ROOT'] . $_SERVER['REQUEST_URI']);
$path = !preg_match('/public/', $pathMatches[0]) ? str_replace($_SERVER['DOCUMENT_ROOT'], $_SERVER['DOCUMENT_ROOT'] . '/public', $pathMatches[0]) : $pathMatches[0];
$filesystem = new Filesystem();

if ($filesystem->exists($path)) {

    $file = new File($path);
    $file = new UploadedFile($file->getPathname(), $file->getFilename(), $file->getMimeType(), NULL, true);
    $extension = $file->getExtension();

    $mimeType = $file->getMimeType();
    if ($extension === 'css') {
        $mimeType = 'text/css';
        header('Content-type: ' . $mimeType);
    } elseif ($extension === 'js') {
        $mimeType = 'application/javascript';
        header('Content-type: ' . $mimeType);
    }

    $isResource = $extension == ('css' || 'js');

    if ($isValid) {

        header('Content-type: ' . $mimeType);

        if (is_file($path)) {
            readfile($path);
        } elseif ($isResource) {
            include 'denied.php';
        } else {
            generateImage('Acces denied');
        }
    } else {

        if ($isResource) {
            include 'denied.php';
        } else {
            generateImage('Not found');
        }
    }
} else {
    generateImage('Not found');
}

/**
 * Generate image
 *
 * @param string $message
 */
function generateImage(string $message)
{
    $img = imagecreatetruecolor(150, 45);
    $color = imagecolorallocate($img, 84, 182, 33);
    imagestring($img, 15, 15, 15, $message, $color);

    header('Content-type: image/jpeg');
    imagejpeg($img);
    imagedestroy($img);
}