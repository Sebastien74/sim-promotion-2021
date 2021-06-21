<?php

namespace App\Service\Translation;

use App\Entity\Core\Website;
use App\Entity\Translation\i18n;
use App\Entity\Translation\Translation;
use App\Entity\Translation\TranslationDomain;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\MappingException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * ExportService
 *
 * Generate ZipArchive of translations files
 *
 * @property EntityManagerInterface $entityManager
 * @property KernelInterface $kernel
 * @property string $dirname
 * @property Website $website
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ExportService
{
    private $entityManager;
    private $kernel;
    private $dirname;
    private $website;

    /**
     * ExportService constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param KernelInterface $kernel
     */
    public function __construct(EntityManagerInterface $entityManager, KernelInterface $kernel)
    {
        $this->entityManager = $entityManager;
        $this->kernel = $kernel;
        $dirname = $this->kernel->getProjectDir() . '/bin/export';
        $this->dirname  = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $dirname);
    }

    /**
     * Execute exportation
     *
     * @param Website $website
     * @throws Exception
     * @throws MappingException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function execute(Website $website)
    {
        $this->website = $website;

        $this->removeXlsxFiles();

        $defaultLocale = $website->getConfiguration()->getLocale();
        $locales = $website->getConfiguration()->getLocales();

        $i18ns = $this->getI18ns();
        $i18ns = $this->generateI18ns($i18ns, $defaultLocale, $locales);
        $this->generateCsvI18ns($i18ns, $defaultLocale);

        $translations = $this->getTranslations($defaultLocale);
        $this->generateCsvTranslations($translations, $defaultLocale);
    }

    /**
     * Generate ZipArchive
     *
     * @return string
     */
    public function zip()
    {
        $finder = new Finder();
        $finder->files()->in($this->dirname)->name('*.xlsx');
        $zip = new \ZipArchive();
        $zipName = 'translations.zip';
        $zip->open($zipName, \ZipArchive::CREATE);

        foreach ($finder as $file) {
            $zip->addFromString($file->getFilename(), $file->getContents());
        }

        $zip->close();

        return $finder->count() ? $zipName : false;
    }

    /**
     * Remove old Xlsx files
     */
    private function removeXlsxFiles()
    {
        $filesystem = new Filesystem();
        $finder = new Finder();
        $finder->files()->in($this->dirname)->name('*.xlsx');

        foreach ($finder as $file) {
            $filesystem->remove($file->getRealPath());
        }
    }

    /**
     * Get all i18n
     *
     * @return array
     */
    private function getI18ns()
    {
        $metaData = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $i18ns = [];

        foreach ($metaData as $data) {

            if ($data->getReflectionClass()->getModifiers() === 0) {

                $namespace = $data->getName();
                $referEntity = new $namespace();
                $tableName = $this->entityManager->getClassMetadata($namespace)->getTableName();

                if (method_exists($referEntity, 'getI18ns') || method_exists($referEntity, 'getI18n')) {

                    if (method_exists($referEntity, 'getWebsite')) {
                        $entities = $this->entityManager->getRepository($namespace)->findByWebsite($this->website);
                    } else {
                        $entities = $this->entityManager->getRepository($namespace)->findAll();
                    }

                    $isCollection = method_exists($referEntity, 'getI18ns');

                    /** @var i18n $i18n */
                    foreach ($entities as $entity) {

                        if ($isCollection) {
                            foreach ($entity->getI18ns() as $i18n) {
                                $i18ns[$tableName][$entity->getId()][$i18n->getLocale()] = (object)['entity' => $entity, 'i18n' => $i18n, 'isCollection' => true];
                            }
                        } else {

                            $i18n = $entity->getI18n() ? $entity->getI18n() : $this->addI18n($isCollection, $tableName, $entity, $entity->getLocale(), NULL);

                            if ($i18n) {
                                $i18ns[$tableName][$entity->getId()][$i18n->getLocale()] = (object)['entity' => $entity, 'i18n' => $i18n, 'isCollection' => false];
                            }
                        }
                    }
                }
            }
        }

        return $i18ns;
    }

    /**
     * Generate non-existent i18n
     *
     * @param array $i18ns
     * @param string $defaultLocale
     * @param array $websiteLocales
     * @return array
     */
    private function generateI18ns(array $i18ns, string $defaultLocale, array $websiteLocales)
    {
        foreach ($i18ns as $tableName => $entity) {

            $defaultEntity = NULL;
            $existingLocales = [];
            $i18nsLocales = [];

            foreach ($entity as $locales) {

                $defaultEntity = !empty($locales[$defaultLocale]) ? $locales[$defaultLocale] : NULL;
                /** @var i18n $defaultI18n */
                $defaultI18n = $defaultEntity ? $defaultEntity->i18n : NULL;

                // Get default locale entity and check existing locale i18n
                foreach ($locales as $locale => $infos) {
                    if ($locale !== $defaultLocale) {
                        $existingLocales[] = $locale;
                        $i18nsLocales[$locale] = $infos->i18n;
                    }
                }

                // Check ans generate non-existent i18n
                foreach ($websiteLocales as $locale) {
                    if ($defaultEntity) {
                        $entity = $defaultEntity->entity;
                        if ($defaultI18n && !in_array($locale, $existingLocales)) {
                            $isCollection = $defaultEntity->isCollection;
                            $i18n = $this->addI18n($isCollection, $tableName, $entity, $locale, $defaultI18n);
                            $i18ns[$tableName][$entity->getId()][$locale] = (object)['entity' => $entity, 'i18n' => $i18n, 'isCollection' => false, 'defaultI18n' => $defaultI18n];
                        } else {
                            $i18ns[$tableName][$entity->getId()][$locale] = (object)['entity' => $entity, 'i18n' => $i18nsLocales[$locale], 'isCollection' => false, 'defaultI18n' => $defaultI18n];
                        }
                    }
                }
            }
        }

        return $i18ns;
    }

    /**
     * Add i18n
     *
     * @param bool $isCollection
     * @param string $tableName
     * @param $entity
     * @param string $locale
     * @param i18n|null $defaultI18n
     * @return i18n
     */
    private function addI18n(bool $isCollection, string $tableName, $entity, string $locale, i18n $defaultI18n = NULL)
    {
        $defaultI18n = $defaultI18n ? $defaultI18n : new i18n();

        $newI18n = new i18n();
        $newI18n->setLocale($locale);
        $newI18n->setTitleForce($defaultI18n->getTitleForce());
        $newI18n->setTitleAlignment($defaultI18n->getTitleAlignment());
        $newI18n->setBodyAlignment($defaultI18n->getBodyAlignment());
        $newI18n->setIntroductionAlignment($defaultI18n->getIntroductionAlignment());
        $newI18n->setTargetAlignment($defaultI18n->getTargetAlignment());
        $newI18n->setTargetStyle($defaultI18n->getTargetStyle());
        $newI18n->setTargetPage($defaultI18n->getTargetPage());
        $newI18n->setWebsite($this->website);

        $setter = $isCollection ? 'addI18n' : 'setI18n';
        $entity->$setter($newI18n);

        $i18ns[$tableName][$entity->getId()][$newI18n->getLocale()] = (object)['entity' => $entity, 'i18n' => $newI18n, 'isCollection' => $isCollection, 'defaultI18n' => $defaultI18n];

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $newI18n;
    }

    /**
     * Generate i18ns CSV
     *
     * @param array $i18ns
     * @param string $defaultLocale
     * @throws MappingException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws Exception
     */
    private function generateCsvI18ns(array $i18ns, string $defaultLocale)
    {
        $i18nFields = $this->getI18nFields();
        $fileData = $this->getI18nFileData($i18ns, $defaultLocale, $i18nFields);

        foreach ($fileData as $tableName => $locales) {
            foreach ($locales as $locale => $entities) {

                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setCellValue($this->getCsvI18nsIndex('locale') . 1, 'locale');
                $sheet->getColumnDimension($this->getCsvI18nsIndex('locale'))->setAutoSize(true);
                $sheet->setCellValue($this->getCsvI18nsIndex('website') . 1, 'website');
                $sheet->getColumnDimension($this->getCsvI18nsIndex('locale'))->setAutoSize(true);

                foreach ($i18nFields as $key => $field) {
                    if (!empty($this->getCsvI18nsIndex($field->field))) {
                        $sheet->setCellValue($this->getCsvI18nsIndex($field->field) . 1, $field->field);
                        $sheet->getColumnDimension($this->getCsvI18nsIndex($field->field))->setAutoSize(true);
                        foreach ($entities as $entityKey => $entity) {
                            $sheet->setCellValue($this->getCsvI18nsIndex('locale') . ($entityKey + 2), $locale);
                            $sheet->setCellValue($this->getCsvI18nsIndex('website') . ($entityKey + 2), $this->website->getId());
                            $sheet->setCellValue($this->getCsvI18nsIndex($field->field) . ($entityKey + 2), $entity[$field->field]);
                        }
                    }
                }

                $filename = $tableName . '-' . $locale . '.xlsx';
                $excelFilepath = $this->dirname . '/' . $filename;
                $writer = new Xlsx($spreadsheet);
                $writer->save($excelFilepath);
            }
        }
    }

    /**
     * Generate i18ns file data
     *
     * @param array $i18ns
     * @param string $defaultLocale
     * @param array $i18nFields
     * @return array
     */
    private function getI18nFileData(array $i18ns, string $defaultLocale, array $i18nFields)
    {
        $fileData = [];

        foreach ($i18ns as $tableName => $entity) {
            foreach ($entity as $locales) {
                foreach ($locales as $locale => $info) {
                    if ($locale !== $defaultLocale) {
                        if (property_exists($info, 'defaultI18n')) {

                            /** @var i18n $defaultI18n */
                            $defaultI18n = $info->defaultI18n;
                            /** @var i18n $localeI18n */
                            $localeI18n = $info->i18n;

                            $defaultCount = $this->getI18nContentCount($defaultI18n, $i18nFields);
                            $haveContent = $this->getI18nHaveContent($defaultI18n, $localeI18n, $i18nFields);

                            if ($defaultCount > 0 && $haveContent) {

                                $entityData = [];
                                foreach ($i18nFields as $field) {
                                    $getter = $field->getter;
                                    if ($field->field === 'id') {
                                        $entityData['id'] = $localeI18n->getId();
                                    } else {
                                        $localeContentLength = strlen(strip_tags($localeI18n->$getter()));
                                        $entityData[$field->field] = $localeContentLength === 0 ? $defaultI18n->$getter() : NULL;
                                    }
                                }

                                $fileData[$tableName][$locale][] = $entityData;
                            }
                        }
                    }
                }
            }
        }

        return $fileData;
    }

    /**
     * Get fields content count
     *
     * @param i18n $i18n
     * @param array $i18nFields
     * @return int
     */
    private function getI18nContentCount(i18n $i18n, array $i18nFields)
    {
        $count = 0;

        foreach ($i18nFields as $field) {
            $getter = $field->getter;
            $contentLength = strlen(strip_tags($i18n->$getter()));
            if ($contentLength > 0 && $field->field !== 'id') {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Check if have content to translate
     *
     * @param i18n $defaultI18n
     * @param i18n $localeI18n
     * @param array $i18nFields
     * @return bool
     */
    private function getI18nHaveContent(i18n $defaultI18n, i18n $localeI18n, array $i18nFields)
    {
        foreach ($i18nFields as $field) {

            $getter = $field->getter;
            $defaultContentLength = strlen(strip_tags($defaultI18n->$getter()));
            $localeContentLength = strlen(strip_tags($localeI18n->$getter()));

            if ($field->field !== 'id' && $defaultContentLength > 0 && $localeContentLength === 0) {
                return true;
            }
        }

        return false;
    }


    /**
     * Get column index
     *
     * @param string $column
     * @return mixed
     */
    private function getCsvI18nsIndex(string $column)
    {
        $indexes = [
            'locale' => 'A',
            'website' => 'B',
            'id' => 'C',
            'title' => 'D',
            'subTitle' => 'E',
            'introduction' => 'F',
            'body' => 'G',
            'targetLink' => 'H',
            'targetLabel' => 'I',
            'placeholder' => 'J',
            'help' => 'K',
            'error' => 'L'
        ];

        return !empty($indexes[$column]) ? $indexes[$column] : NULL;
    }

    /**
     * Get Translations
     *
     * @param string $defaultLocale
     * @return array
     */
    private function getTranslations(string $defaultLocale)
    {
        $translations = [];
        $domains = $this->entityManager->getRepository(TranslationDomain::class)->findAll();

        foreach ($domains as $domain) {
            if ($domain->getExtract()) {
                foreach ($domain->getUnits() as $unit) {
                    foreach ($unit->getTranslations() as $translation) {
                        if ($translation->getLocale() !== $defaultLocale && !$translation->getContent()) {
                            $translations[$translation->getLocale()][] = $translation;
                        }
                    }
                }
            }
        }

        return $translations;
    }

    /**
     * Generate translations CSV
     *
     * @param array $translations
     * @param string $defaultLocale
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function generateCsvTranslations(array $translations, string $defaultLocale)
    {
        foreach ($translations as $locale => $localeTranslation) {

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setCellValue('A1', 'locale');
            $sheet->setCellValue('B1', 'domain');
            $sheet->setCellValue('C1', 'id');
            $sheet->setCellValue('D1', 'content');
            $sheet->setCellValue('E1', 'translation');

            foreach ($localeTranslation as $key => $translation) {

                /** @var Translation $translation */

                $defaultContent = NULL;
                foreach ($translation->getUnit()->getTranslations() as $unitTranslation) {
                    if ($unitTranslation->getLocale() === $defaultLocale) {
                        $defaultContent = $unitTranslation->getContent();
                        break;
                    }
                }

                if ($defaultContent) {
                    $sheet->setCellValue('A' . ($key + 2), $translation->getLocale());
                    $sheet->setCellValue('B' . ($key + 2), $translation->getUnit()->getDomain()->getName());
                    $sheet->setCellValue('C' . ($key + 2), $translation->getId());
                    $sheet->setCellValue('D' . ($key + 2), $defaultContent);
                    $sheet->setCellValue('E' . ($key + 2), '');
                }
            }

            $excelFilepath = $this->dirname . '/translations-' . $locale . '.xlsx';
            $writer = new Xlsx($spreadsheet);
            $writer->save($excelFilepath);
        }
    }

    /**
     * Get i18n text fields
     *
     * @return array
     * @throws MappingException
     */
    private function getI18nFields()
    {
        $referI18n = new i18n();
        $i18nMetadata = $this->entityManager->getClassMetadata(i18n::class);
        $i18nAllFields = $i18nMetadata->getFieldNames();
        $allowedFields = ['string', 'text'];
        $i18nFields = [];

        foreach ($i18nAllFields as $field) {
            $getter = 'get' . ucfirst($field);
            $mapping = $i18nMetadata->getFieldMapping($field);
            $isText = in_array($mapping['type'], $allowedFields) && !preg_match('/alignment/', strtolower($mapping['fieldName'])) && $field !== 'locale';
            if (method_exists($referI18n, $getter) && $isText || $field === 'id') {
                $i18nFields[] = (object)['getter' => $getter, 'field' => $field];
            }
        }

        return $i18nFields;
    }
}