<?php

namespace App\Service\Translation;

use App\Command\CacheCommand;
use App\Entity\Translation\i18n;
use App\Entity\Translation\Translation;
use App\Service\Core\XlsxFileReader;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * ImportService
 *
 * Import translation by Xlxs files
 *
 * @property EntityManagerInterface $entityManager
 * @property XlsxFileReader $fileReader
 * @property KernelInterface $kernel
 * @property CacheCommand $cacheCommand
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ImportService
{
    private $entityManager;
    private $fileReader;
    private $kernel;
    private $cacheCommand;

    /**
     * ImportService constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param XlsxFileReader $fileReader
     * @param KernelInterface $kernel
     * @param CacheCommand $cacheCommand
     */
    public function __construct(EntityManagerInterface $entityManager, XlsxFileReader $fileReader, KernelInterface $kernel, CacheCommand $cacheCommand)
    {
        $this->entityManager = $entityManager;
        $this->fileReader = $fileReader;
        $this->kernel = $kernel;
        $this->cacheCommand = $cacheCommand;
    }

    /**
     * Execute import
     *
     * @param array $files
     * @throws Exception
     */
    public function execute(array $files)
    {
        $namespaces = $this->getNamespaces();

        foreach ($files as $file) {

            $data = $this->fileReader->read($file);
            $namespace = $this->getRepository($file, $namespaces);

            if ($namespace === Translation::class) {
                $this->setTranslations($data->iterations);
            } elseif ($namespace) {
                $this->setI18ns($data->iterations);
            }
        }

        $this->cacheCommand->clear();
    }

    /**
     * Get all namespaces
     *
     * @return array
     */
    private function getNamespaces()
    {
        $namespaces = [];
        $metaData = $this->entityManager->getMetadataFactory()->getAllMetadata();

        foreach ($metaData as $data) {
            if ($data->getReflectionClass()->getModifiers() === 0) {
                $namespace = $data->getName();
                $tableName = $this->entityManager->getClassMetadata($namespace)->getTableName();
                $namespaces[$tableName] = $data->getName();
            }
        }

        $namespaces['translations'] = Translation::class;

        return $namespaces;
    }

    /**
     * Get entity repository
     *
     * @param UploadedFile $file
     * @param array $tables
     * @return mixed|null
     */
    private function getRepository(UploadedFile $file, array $tables)
    {
        $matches = explode('.', str_replace('.xlsx', '', $file->getClientOriginalName()));
        $matches = !empty($matches[0]) ? explode('-', $matches[0]) : NULL;
        $tableName = !empty($matches[0]) ? $matches[0] : NULL;

        return !empty($tables[$tableName]) ? $tables[$tableName] : NULL;
    }

    /**
     * Set Translation[]
     *
     * @param array $data
     */
    private function setTranslations(array $data)
    {
        $filesystem = new Filesystem();
        $repository = $this->entityManager->getRepository(Translation::class);

        foreach ($data as $translation) {

            if (!empty($translation['id']) && !empty($translation['content']) && !empty($translation['translation'])) {

                $translationDb = $repository->find($translation['id']);
                $translationDb->setContent($translation['content']);

                $this->entityManager->persist($translationDb);
                $this->entityManager->flush();

                $filePath = $this->kernel->getProjectDir() . '/translations/' . $translation['domain'] . '.' . $translation['locale'] . '.yaml';
                $filePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $filePath);
                $values = [];
                if ($filesystem->exists($filePath)) {
                    $values = Yaml::parseFile($filePath);
                }
                $values[$translation['content']] = $translation['translation'];
                ksort($values);
                $yaml = Yaml::dump($values);
                file_put_contents($filePath, $yaml);
            }
        }
    }

    /**
     * Set i18n[]
     *
     * @param array $data
     */
    private function setI18ns(array $data)
    {
        $repository = $this->entityManager->getRepository(i18n::class);
        $excludes = ['locale', 'website', 'id'];

        foreach ($data as $translation) {

            if (!empty($translation['id'])) {

                $i18n = $repository->find($translation['id']);

                foreach ($translation as $property => $value) {
                    $setter = 'set' . ucfirst($property);
                    if ($i18n && !in_array($property, $excludes) && !empty($value) && method_exists($i18n, $setter)) {
                        $i18n->$setter($value);
                    }
                }

                if ($i18n) {
                    $this->entityManager->persist($i18n);
                    $this->entityManager->flush();
                }
            }
        }
    }
}