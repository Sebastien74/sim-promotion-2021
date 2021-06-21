<?php

namespace App\Service\Development;

use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;

class FileUrlizerService
{
    private $kernel;

    /**
     * FileUrlizerService constructor.
     *
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }


    /**
     * Execute urlizer
     *
     * @param array $files
     * @return string
     */
    public function execute(array $files)
    {
        $tmpDirname = $this->kernel->getProjectDir() . '/public/uploads/tmp/rename/';
        $tmpDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $tmpDirname);

        foreach ($files as $file) {

            /** @var UploadedFile $file */

            $extension = $file->guessClientExtension();
            $filename = str_replace('.' . $extension, '', $file->getClientOriginalName());

            $file->move(
                $tmpDirname,
                Urlizer::urlize($filename) . '.' . $extension
            );
        }

        return $this->zip($tmpDirname);
    }

    /**
     * Generate ZipArchive
     *
     * @param string $dirname
     * @param string|null $filename
     * @return string
     */
    public function zip(string $dirname, string $filename = NULL)
    {
        $finder = new Finder();
        $finder->files()->in($dirname);
        $zip = new \ZipArchive();
        $zipName = $filename ? $filename : 'rename-files.zip';
        $zipName = !preg_match('/.zip/', $zipName) ? $zipName . '.zip' : $zipName;
        $zip->open($zipName, \ZipArchive::CREATE);

        foreach ($finder as $file) {
            $zip->addFromString($file->getFilename(), $file->getContents());
        }

        $zip->close();

        return $finder->count() ? $zipName : false;
    }
}