<?php

namespace App\Twig\Content;

use App\Command\AssetsCommand;
use App\Command\JsRoutingCommand;
use App\Entity\Core\Website;
use Exception;
use Symfony\Component\Asset\Package;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * FileRuntime
 *
 * @property KernelInterface $kernel
 * @property string $uploadsPath
 * @property MediaRuntime mediaExtension
 * @property AssetsCommand $assetsCommand
 * @property JsRoutingCommand $jsRoutingCommand
 * @property Filesystem $fileSystem
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FileRuntime implements RuntimeExtensionInterface
{
    private $kernel;
    private $uploadsPath;
    private $mediaExtension;
    private $assetsCommand;
    private $jsRoutingCommand;
    private $fileSystem;

    /**
     * FileRuntime constructor.
     *
     * @param KernelInterface $kernel
     * @param string $uploadsPath
     * @param MediaRuntime $mediaExtension
     * @param AssetsCommand $assetsCommand
     * @param JsRoutingCommand $jsRoutingCommand
     */
    public function __construct(
        KernelInterface $kernel,
        string $uploadsPath,
        MediaRuntime $mediaExtension,
        AssetsCommand $assetsCommand,
        JsRoutingCommand $jsRoutingCommand)
    {
        $this->kernel = $kernel;
        $this->uploadsPath = $uploadsPath;
        $this->mediaExtension = $mediaExtension;
        $this->assetsCommand = $assetsCommand;
        $this->jsRoutingCommand = $jsRoutingCommand;
        $this->fileSystem = new Filesystem();
    }

    /**
     * Check if file exist
     *
     * @param mixed $website
     * @param string|null $filename
     * @return SplFileInfo|null
	 */
    public function fileInfo($website, string $filename = NULL): ?SplFileInfo
    {
        $finder = new Finder();
        $placeholderDirname = NULL;
        $isPlaceHolder = preg_match('/vendor\/images\/placeholder/', $filename);
        $websiteDirname = $website instanceof Website ? $website->getUploadDirname() : $website['uploadDirname'];
        $fileDirname = $this->uploadsPath . '/' . $websiteDirname . '/' . $filename;

        if ($filename && !$this->fileSystem->exists($fileDirname)) {
            $isPlaceHolder = true;
        }

        if ($isPlaceHolder) {
            $projectDirname = $this->kernel->getProjectDir();
            $projectDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $projectDirname);
            $logos = $this->mediaExtension->logos($website);
            $fileDirname = !empty($logos['placeholder']) && $this->fileSystem->exists($projectDirname . '/public' . $logos['placeholder'])
                ? $projectDirname . '/public' . $logos['placeholder']
                : $projectDirname . '/public/medias/placeholder.jpg';
            $placeholderDirname = str_replace($projectDirname . '/public', '', $fileDirname);
            $finder->files()->in('build/vendor/images')->name('placeholder.*.jpg');
        } else {
            $finder->files()->in($this->uploadsPath)->name($filename);
        }

        if ($this->fileSystem->exists($fileDirname) && $filename) {

            $file = new File($fileDirname);
            $infos = new SplFileInfo($file, $file->getPathname(), $file->getFilename());

            if ($infos) {
                try {
                    $sizes = getimagesize($fileDirname);
                    $infos->isImage = @is_array($sizes);
                    $infos->isPlaceHolder = $isPlaceHolder;
                    $infos->dir = $isPlaceHolder ? $placeholderDirname : '/uploads/' . $websiteDirname . '/' . $infos->getRelativePathname();
                    $infos->width = !empty($sizes[0]) ? $sizes[0] : NULL;
                    $infos->height = !empty($sizes[1]) ? $sizes[1] : NULL;
                    $infos->attributes = !empty($sizes[3]) ? $sizes[3] : NULL;
                    $infos->mime = !empty($sizes['mime']) ? $sizes['mime'] : NULL;
                    $infos->bits = !empty($sizes['bits']) ? $sizes['bits'] : NULL;

                } catch (Exception $exception) {}
            }

            return $infos;
        }

        return NULL;
    }

    /**
     * Get file Bytes
     *
     * @param $bytes
     * @param int $precision
     * @return string
     */
    function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytesMax = max($bytes, 0);
        $powFloor = floor(($bytesMax ? log($bytesMax) : 0) / log(1024));
        $powMin = min($powFloor, count($units) - 1);
        $bytesMax /= pow(1024, $powMin);

        return round($bytesMax, $precision) . ' ' . $units[$powMin];
    }

    /**
     * Check if file exist
     *
     * @param string|null $path
     *
     * @param string $dir
     * @return boolean
     */
    public function fileExist(string $path = NULL, $dir = '/templates/')
    {
        if (!$path) {
            return false;
        }

        $fileDir = $dir !== '/templates/' ? '/public/' . $path : $dir . $path;
        $fileDir = str_replace('//', '/', $fileDir);
        $fileDir = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $fileDir);

        try {
            return $this->fileSystem->exists($this->kernel->getProjectDir() . $fileDir);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * To set jsRouting file
     *
     * @param string|null $filename
     * @param bool $all
     */
    public function jsRouting(string $filename = NULL, bool $all = false)
    {
        $filesystem = new Filesystem();
        $masterDirname = $this->kernel->getProjectDir() . '/public/js/fos_js_routes.json';
        $masterDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $masterDirname);
        $routerDirname = $this->kernel->getProjectDir() . '/public/bundles/fosjsrouting/js/router.min.js';
        $routerDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $routerDirname);
        $fileDirname = $this->kernel->getProjectDir() . '/public/js/' . $filename . '.json';
        $fileDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $fileDirname);

        if (!$filesystem->exists($masterDirname)
            || !$filesystem->exists($routerDirname)
            || $filename && !$filesystem->exists($fileDirname)) {

            $this->assetsCommand->install();
            $this->jsRoutingCommand->dump($filename, $all);
        }
    }
}