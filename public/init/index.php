<?php

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Yaml\Yaml;

require dirname(__DIR__) . './../vendor/autoload.php';
require_once 'includes/functions.php';

(new Dotenv())->bootEnv(dirname(__DIR__) . './../.env');

$kernel = new Kernel($_SERVER['APP_ENV'], (bool)$_SERVER['APP_DEBUG']);

/** Check if is new project */
$configDirname = $kernel->getProjectDir() . '/bin/data/config/default.yaml';
$configDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $configDirname);
$configInit = Yaml::parseFile($configDirname);

if ($configInit['is_init']) {
    header('Location: ' . url());
    exit;
}

?>

<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="assets/images/logo.png">

    <!-- CSS -->
    <link rel="stylesheet" href="assets/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/prism.css">
    <link rel="stylesheet" href="assets/css/scrolling-nav.css">
    <link rel="stylesheet" href="assets/css/styles.css">

    <title>New Project - Félix création</title>
</head>

<body>

<?php include 'includes/preloader.php'; ?>
<?php include 'includes/navbar.php'; ?>
<?php include 'includes/header.php'; ?>
<?php include 'sections/vendor.php'; ?>
<?php include 'sections/environment.php'; ?>
<?php include 'sections/yarn.php'; ?>
<?php include 'includes/footer.php'; ?>

<!-- JavaScript -->
<script src="assets/vendor/jquery/jquery.min.js"></script>
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="assets/js/prism.js"></script>
<script src="assets/js/scrolling-nav.js"></script>

</body>
</html>