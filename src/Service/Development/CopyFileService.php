<?php

namespace App\Service\Development;

use App\Entity\Core\Website;
use Monolog\Logger;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * CopyFileService
 *
 * To copy file from path to other path
 *
 * @property LogService $logService
 * @property KernelInterface $kernel
 * @property string $baseDirname
 * @property Filesystem $filesystem
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CopyFileService
{
    private $logService;
    private $kernel;
    private $baseDirname;
    private $filesystem;

    /**
     * CopyFileService constructor.
     *
     * @param KernelInterface $kernel
     * @param LogService $logService
     */
    public function __construct(KernelInterface $kernel, LogService $logService)
    {
        $this->logService = $logService;
        $this->kernel = $kernel;
        $this->baseDirname = $kernel->getProjectDir() . '/public/uploads/';
        $this->filesystem = new Filesystem();
    }

    /**
     * Copy File
     *
     * @param Website $website
     * @param string $path
     * @param string $filename
     * @param string|null $dirname
     * @return bool
     */
    public function copy(Website $website, string $path, string $filename, string $dirname = NULL)
    {
        try {

            $fileExist = $this->filesystem->exists($this->kernel->getProjectDir() . DIRECTORY_SEPARATOR . $path);

            if ($fileExist && !is_dir($path)) {

                $file = new File($path);
                $tmpDirname = $this->baseDirname . 'tmp/' . $file->getFilename();
                $this->filesystem->copy($file->getPathname(), $tmpDirname);

                $tmpFile = new File($tmpDirname);
                $uploadedFile = new UploadedFile($tmpFile->getPathname(), $tmpFile->getFilename(), $tmpFile->getMimeType(), NULL, true);

                if ($uploadedFile) {
                    $uploadedFile->move($this->baseDirname . $website->getUploadDirname() . '/' . $dirname, $filename);
                }

                $this->logService->log('OK', Logger::INFO, 'copy-file', 'Dirname: ' . $path);
            } elseif (is_dir($path)) {
                $this->logService->log('IS_DIR', Logger::WARNING, 'copy-file', 'Dirname: ' . $path);
            } else {
                $this->logService->log('NO_EXISTING_FILE', Logger::WARNING, 'copy-file', 'Dirname: ' . $path);
            }

            return $fileExist;
        } catch (\Exception $exception) {
            $this->logService->log('EXCEPTION', Logger::CRITICAL, 'copy-file', 'Error: ' . $exception->getMessage() . ' for dirname: ' . $path);
            return false;
        }
    }
}