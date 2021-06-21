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
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * ExportContactService
 *
 * To generate export CSV
 *
 * @property EntityManagerInterface $entityManager
 * @property KernelInterface $kernel
 * @property Request $request
 * @property array $alpha
 * @property Worksheet $sheet
 * @property int $headerAlphaIndex
 * @property int $entityAlphaIndex
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ExportContactService
{
    private $entityManager;
    private $kernel;
    private $request;
    private $alphas = [];
    private $sheet;
    private $headerAlphaIndex = 0;
    private $entityAlphaIndex = 0;

    /**
     * ExportContactService constructor.
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
     * Set Header
     *
     * @param $referEntity
     * @param array $exportFields
     */
    private function setHeader($referEntity, array $exportFields = [])
    {
        foreach ($exportFields as $key => $fieldName) {
            $getter = 'get' . ucfirst($fieldName);
            if (method_exists($referEntity, $getter) && !$referEntity->$getter() instanceof PersistentCollection && !$referEntity->$getter() instanceof ArrayCollection) {
                $this->sheet->setCellValue($this->alphas[$this->headerAlphaIndex] . 1, $fieldName);
                $this->sheet->getColumnDimension($this->alphas[$this->headerAlphaIndex])->setAutoSize(true);
                $this->headerAlphaIndex++;
            } elseif ($referEntity instanceof ContactForm && $fieldName === "contactValues") {
                $this->setContactFormHeader();
            }
        }
    }

    /**
     * Set ContactForm Header
     */
    private function setContactFormHeader()
    {
        $form = $this->entityManager->getRepository(Form::class)->find($this->request->get('form'));
        $zones = $form->getLayout()->getZones();
        $excluded = [SubmitType::class];

        $values = [];
        foreach ($zones as $zone) {
            foreach ($zone->getCols() as $col) {
                foreach ($col->getBlocks() as $block) {
                    if (!in_array($block->getBlockType()->getFieldType(), $excluded)) {
                        $values[$block->getId()] = $this->getI18nEntitled($block);
                    }
                }
            }
        }

        ksort($values);

        foreach ($values as $name) {
            $this->sheet->setCellValue($this->alphas[$this->headerAlphaIndex] . 1, $name);
            $this->sheet->getColumnDimension($this->alphas[$this->headerAlphaIndex])->setAutoSize(true);
            $this->headerAlphaIndex++;
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
                if (method_exists($entity, $getter) && !$entity->$getter() instanceof PersistentCollection && !$referEntity->$getter() instanceof ArrayCollection) {
                    $this->sheet->setCellValue($this->alphas[$this->entityAlphaIndex] . $indexEntity, $entity->$getter());
                    $this->entityAlphaIndex++;
                } elseif ($referEntity instanceof ContactForm && $fieldName === "contactValues") {
                    $this->setContactValues($indexEntity, $entity);
                }
            }
            $indexEntity++;
        }
    }

    /**
     * Set ContactValues
     *
     * @param int $indexEntity
     * @param mixed $entity
     */
    private function setContactValues(int $indexEntity, $entity)
    {
        $excluded = [SubmitType::class];
        $values = [];

        foreach ($entity->getContactValues() as $value) {
            /** @var ContactValue $value */
            $block = $value->getConfiguration()->getBlock();
            if (!in_array($block->getBlockType()->getFieldType(), $excluded)) {
                $values[$block->getId()] = $value->getValue();
            }
        }

        ksort($values);

        foreach ($values as $value) {
            $this->sheet->setCellValue($this->alphas[$this->entityAlphaIndex] . $indexEntity, $value);
            $this->entityAlphaIndex++;
        }

    }

    /**
     * Get i18n entitled
     *
     * @param mixed $entity
     * @param string $field
     * @return string|null
     */
    private function getI18nEntitled($entity, $field = 'title')
    {
        $getter = 'get' . ucfirst($field);
        $entitled = method_exists($entity, 'getAdminName') && $entity->getAdminName() ? $entity->getAdminName() : NULL;

        if (method_exists($entity, 'getI18ns')) {
            foreach ($entity->getI18ns() as $i18n) {
                /** @var i18n $i18n */
                if (method_exists($entity, $getter) && $i18n->$getter && $i18n->getLocale() === $this->request->getLocale()) {
                    $entitled = $i18n->$getter;
                }
            }
        }

        return $entitled;
    }
}