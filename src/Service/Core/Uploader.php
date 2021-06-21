<?php

namespace App\Service\Core;

use App\Entity\Core\Website;
use App\Twig\Core\WebsiteRuntime;
use Doctrine\ORM\NonUniqueResultException;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Uploader
 *
 * Manage Uploaded File
 *
 * @property string $uploadsPath
 * @property string $uploadsBasePath
 * @property TranslatorInterface $translator
 * @property string $filename
 * @property string $name
 * @property string $extension
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Uploader
{
    private $uploadsPath;
    private $uploadsBasePath;
    private $translator;
    private $filename;
    private $name;
    private $extension;

    /**
     * Uploader constructor.
     *
     * @param string $uploadsPath
     * @param TranslatorInterface $translator
     * @param WebsiteRuntime $websiteExtension
     * @throws NonUniqueResultException
     */
    public function __construct(string $uploadsPath, TranslatorInterface $translator, WebsiteRuntime $websiteExtension)
    {
        $this->uploadsBasePath = $uploadsPath;
        $this->translator = $translator;

        $website = $websiteExtension->website();
        if ($website) {
            $this->uploadsPath = $uploadsPath . '/' . $website->getUploadDirname();
        }
    }

    /**
     * Upload File
     *
     * @param UploadedFile $uploadedFile
     * @param Website $website
     * @param string|null $uploadsPath
     * @return bool
     */
    public function upload(UploadedFile $uploadedFile, Website $website, string $uploadsPath = NULL)
    {
        $this->setFilename($uploadedFile, $website);

        if (!$uploadedFile->guessExtension()) {
            $message = $this->translator->trans("Une erreur est survenue : L'extension du ficher n'a pas pu être déterminée.", [], 'messages') . ' <strong>(' . $uploadedFile->getClientOriginalName() . ')</strong>';
            $session = new Session();
            $session->getFlashBag()->add('error', $message);
            return false;
        }

        $uploadDirname = $uploadsPath ? $uploadsPath : $this->uploadsBasePath . '/' . $website->getUploadDirname();

        $uploadedFile->move(
            $uploadDirname,
            $this->filename
        );

        return true;
    }

    /**
     * Create an UploadedFile object from absolute path
     *
     * @param string $path
     * @param bool $public
     * @param string|null $tmpDirname
     * @return UploadedFile
     */
    public function pathToUploadedFile(string $path, bool $public = true, string $tmpDirname = NULL)
    {
        $filesystem = new Filesystem();

        if($tmpDirname && !$filesystem->exists($tmpDirname)) {
            $filesystem->mkdir($tmpDirname, 0777);
        }

        if ($filesystem->exists($path)) {

            $file = new File($path);
            $tmpDirname = $tmpDirname ? $tmpDirname . $file->getFilename() : $this->uploadsBasePath . '/tmp/' . $file->getFilename();

            $filesystem->copy($file->getPathname(), $tmpDirname);

            $tmpFile = new File($tmpDirname);
            return new UploadedFile($tmpFile->getPathname(), $tmpFile->getFilename(), $tmpFile->getMimeType(), NULL, $public);
        }
    }

    /**
     * Get filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Get name without extension
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get filename
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * Set filename
     *
     * @param UploadedFile $uploadedFile
     * @param Website $website
     */
    private function setFilename(UploadedFile $uploadedFile, Website $website): void
    {
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $urlizeFilename = Urlizer::urlize($originalFilename);
        $filesystem = new Filesystem();
        $existingFile = $filesystem->exists($this->uploadsBasePath . '/' . $website->getUploadDirname() . '/' . $urlizeFilename . '.' . $uploadedFile->guessExtension());
        $this->name = !$existingFile
            ? $urlizeFilename
            : $urlizeFilename . '-' . uniqid();
        $this->filename = preg_match('/webmanifest/', $uploadedFile->getClientOriginalName())
            ? $this->name . '.webmanifest'
            : $this->name . '.' . $uploadedFile->guessExtension();
        $this->extension = $uploadedFile->guessExtension();

        if ($existingFile) {
            $session = new Session();
            $session->getFlashBag()->add('warning', $uploadedFile->getClientOriginalName() . " " . $this->translator->trans('a été renommé car un fichier du même nom existait déja.', [], 'admin'));
        }
    }

    /**
     * Remove file
     *
     * @param string|null $filename
     */
    public function removeFile(string $filename = NULL)
    {
        if ($filename) {

            $dirname = $this->uploadsPath . '/' . $filename;
            $filesystem = new Filesystem();

            if ($filename && $filesystem->exists($dirname)) {
                $filesystem->remove($dirname);
            }
        }
    }

    /**
     * Rename file
     *
     * @param string $originalName
     * @param string $filename
     * @param string $extension
     * @return bool
     */
    public function rename(string $originalName, string $filename, string $extension)
    {
        $originalDirname = $this->uploadsPath . '/' . $originalName . '.' . $extension;
        $dirname = $this->uploadsPath . '/' . Urlizer::urlize($filename) . '.' . $extension;
        $filesystem = new Filesystem();
        $existingFile = $filesystem->exists($dirname);
        if ($filename && $originalName && $filesystem->exists($originalDirname) && !$existingFile) {
            $filesystem->rename($originalDirname, $dirname);
            return true;
        }
        return false;
    }
}