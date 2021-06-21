<?php

namespace App\Service\Export;

use App\Entity\Module\Form\ContactForm;
use App\Entity\Module\Form\ContactValue;
use App\Entity\Module\Form\Form;
use App\Entity\Translation\i18n;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\PersistentCollection;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * ExportCsvService
 *
 * To generate export CSV
 *
 * @property EntityManagerInterface $entityManager
 * @property KernelInterface $kernel
 * @property Request $request
 * @property array $alpha
 * @property array $yamlInfos
 * @property Worksheet $sheet
 * @property int $headerAlphaIndex
 * @property int $entityAlphaIndex
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ExportCsvService
{
    private $entityManager;
    private $kernel;
    private $request;
    private $alphas = [];
    private $yamlInfos = [];
    private $sheet;
    private $headerAlphaIndex = 0;
    private $entityAlphaIndex = 0;

    /**
     * ExportCsvService constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param KernelInterface $kernel
     * @param RequestStack $requestStack
     */
    public function __construct(EntityManagerInterface $entityManager, KernelInterface $kernel, RequestStack $requestStack)
    {
        $this->entityManager = $entityManager;
        $this->kernel = $kernel;
        $this->request = $requestStack->getMasterRequest();
    }

    public function execute(array $entities, array $interface)
    {
        $referEntity = new $interface['classname']();
        $configuration = !empty($interface['configuration']) ? $interface['configuration'] : NULL;
        $exportFields = $configuration ? $configuration->exports : [];
        $spreadsheet = new Spreadsheet();
        $this->alphas = range('A', 'Z');

        try {
            $this->sheet = $spreadsheet->getActiveSheet();
        } catch (Exception $e) {
        }

        $this->setYamlInfos($interface);
        $this->setHeader($referEntity, $exportFields);
        $this->setBody($referEntity, $entities, $exportFields);

        $excelFilepath = $this->kernel->getProjectDir() . '/bin/export/' . $interface['name'] . '.xlsx';
        $excelFilepath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $excelFilepath);
        $writer = new Xlsx($spreadsheet);
        try {
            $writer->save($excelFilepath);
        } catch (\PhpOffice\PhpSpreadsheet\Writer\Exception $e) {
        }

        $response = new Response(file_get_contents($excelFilepath));
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $excelFilepath . '"');
        $response->headers->set('Content-length', filesize($excelFilepath));

        return $response;
    }

    /**
     * Set Yaml entity infos
     *
     * @param array $interface
     */
    private function setYamlInfos(array $interface = [])
    {
        $interfaceName = !empty($interface['name']) ? $interface['name'] : NULL;

        if ($interfaceName) {
            $fileDirname = $this->kernel->getProjectDir() . '/bin/data/export/' . $interfaceName . '.yaml';
            $fileDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $fileDirname);
            $filesystem = new Filesystem();
            if ($filesystem->exists($fileDirname)) {
                $this->yamlInfos = Yaml::parseFile($fileDirname);
            }
        }
    }

    /**
     * Set Header
     *
     * @param $referEntity
     * @param array $exportFields
     */
    private function setHeader($referEntity, array $exportFields = [])
    {
        foreach ($exportFields as $key => $fieldName) {
            $getter = 'get' . ucfirst($fieldName);
            if (method_exists($referEntity, $getter) && !$referEntity->$getter() instanceof PersistentCollection && !$referEntity->$getter() instanceof ArrayCollection || preg_match('/./', $fieldName)) {
                $name = !empty($this->yamlInfos['columns'][$fieldName]) ? $this->yamlInfos['columns'][$fieldName] : $fieldName;
                $this->sheet->setCellValue($this->alphas[$this->headerAlphaIndex] . 1, $name);
                $this->sheet->getColumnDimension($this->alphas[$this->headerAlphaIndex])->setAutoSize(true);
                $this->headerAlphaIndex++;
            }
        }
    }

    /**
     * Set Body
     *
     * @param $referEntity
     * @param array $entities
     * @param array $exportFields
     */
    private function setBody($referEntity, array $entities = [], array $exportFields = [])
    {
        $indexEntity = 2;

        foreach ($entities as $entity) {

            $this->entityAlphaIndex = 0;

            foreach ($exportFields as $key => $fieldName) {

                $getter = 'get' . ucfirst($fieldName);

                if (method_exists($entity, $getter) && $entity->$getter() instanceof \DateTime) {
                    $value = $entity->$getter()->format('Y-m-d H:i:s');
                    $this->sheet->setCellValue($this->alphas[$this->entityAlphaIndex] . $indexEntity, $value);
                    $this->entityAlphaIndex++;
                } elseif (method_exists($entity, $getter) && !$entity->$getter() instanceof PersistentCollection && !$referEntity->$getter() instanceof ArrayCollection) {
                    $value = !empty($this->yamlInfos['values'][$entity->$getter()]) ? $this->yamlInfos['values'][$entity->$getter()] : NULL;
                    $value = $value ? $value : $entity->$getter();
                    $this->sheet->setCellValue($this->alphas[$this->entityAlphaIndex] . $indexEntity, $value);
                    $this->entityAlphaIndex++;
                } elseif (preg_match('/./', $fieldName)) {
                    $associationsFields = explode('.', $fieldName);
                    $associationValue = $entity;
                    foreach ($associationsFields as $associationFieldName) {
                        $getter = 'get' . ucfirst($associationFieldName);
                        if (method_exists($associationValue, $getter) && !$associationValue->$getter() instanceof PersistentCollection) {
                            $associationValue = $associationValue->$getter();
                        }
                    }
                    $associationValue = is_string($associationValue) ? $associationValue : NULL;
                    $this->sheet->setCellValue($this->alphas[$this->entityAlphaIndex] . $indexEntity, $associationValue);
                    $this->entityAlphaIndex++;
                }
            }

            $indexEntity++;
        }
    }
}