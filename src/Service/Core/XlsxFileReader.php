<?php

namespace App\Service\Core;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * ImportRedirectionManager
 *
 * Read Xlsx file
 *
 * @property array $mapping
 * @property array $iterations
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class XlsxFileReader
{
    private $mapping = [];
    private $iterations = [];

    /**
     * Read UploadedFile
     *
     * @param UploadedFile $tmpFile
     * @return object
     * @throws Exception
     */
    public function read(UploadedFile $tmpFile)
    {
        $this->mapping = [];
        $this->iterations = [];

        $inputFileType = 'Xlsx';
        $reader = IOFactory::createReader($inputFileType);
        $spreadsheet = $reader->load($tmpFile);

        foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
            $tabTitle = $this->nameFormatter($worksheet->getTitle());
            $this->getMapping($worksheet, $tabTitle);
            $this->getIterations($worksheet, $tabTitle);
        }

        return (object)[
            'mapping' => $this->mapping,
            'iterations' => $this->iterations,
        ];
    }

    /**
     * Get Header mapping
     *
     * @param mixed $worksheet
     * @param string $tabTitle
     */
    private function getMapping($worksheet, string $tabTitle)
    {
        foreach ($worksheet->getRowIterator() as $row) {

            $rowIndex = $row->getRowIndex();
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            foreach ($cellIterator as $letter => $cell) {
                if ($rowIndex === 1 && !empty(trim($cell->getCalculatedValue()))) {
                    $this->mapping[$tabTitle][$letter] = $this->nameFormatter($cell->getCalculatedValue());
                }
            }
        }
    }

    /**
     * Set Xls iteration
     *
     * @param mixed $worksheet
     * @param string $tabTitle
     */
    private function getIterations($worksheet, string $tabTitle)
    {
        // Generate data to parse
        foreach ($worksheet->getRowIterator() as $row) {

            $rowIndex = $row->getRowIndex();
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            foreach ($cellIterator as $letter => $cell) {
                if (!is_null($cell)) {
                    if ($rowIndex > 1 && !empty($this->mapping[$tabTitle][$letter])) {
                        $value = is_numeric($cell->getCalculatedValue()) ? $cell->getCalculatedValue() : rtrim($cell->getCalculatedValue(), '%20');
                        $this->iterations[$rowIndex][$this->mapping[$tabTitle][$letter]] = $value;
                    }
                }
            }
        }
    }

    /**
     * Init String without specials chars
     *
     * @param string $string
     * @param string $remplacment
     * @return String
     */
    private function nameFormatter(string $string, string $remplacment = "_")
    {
        $string = utf8_encode($string);
        $string = str_replace(['[\', \']'], '', $string);
        $string = preg_replace('/&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);/i', '\\1', $string);
        $string = preg_replace('/\[.*\]/U', '', $string);
        $string = htmlentities($string, ENT_COMPAT, 'utf-8');
        $string = htmlentities($string, ENT_IGNORE, 'utf-8');
        $string = preg_replace('/&(amp;)?#?[a-z0-9]+;/i', $remplacment, $string);
        $string = preg_replace(['/[^a-z0-9]/i', '/[-]+/'], $remplacment, $string);
        $string = substr($string, -1) == "-" ? rtrim($string, $remplacment) : $string;
        $string = trim($string);
        $string = str_replace('__', '', $string);

        return $string;
    }
}