<?php

namespace App\Twig\Content;

use App\Entity\Module\Table\Table;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * TableRuntime
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TableRuntime implements RuntimeExtensionInterface
{
    /**
     * Order Table entity for view
     *
     * @param Table $table
     * @param string $locale
     * @return array
     */
    public function table(Table $table, string $locale)
    {
        $results = array();

        foreach ($table->getCols() as $col) {

            $colLocale = NULL;

            foreach ($col->getI18ns() as $colI18n) {
                if ($colI18n->getLocale() === $locale) {
                    $colLocale = $colI18n;
                }
            }

            $results['head']['col-' . $col->getPosition()] = array(
                'entity' => $col,
                'i18n' => $colLocale
            );

            ksort($results['head']);

            foreach ($col->getCells() as $cell) {

                $cellLocale = NULL;

                foreach ($cell->getI18ns() as $cellI18n) {
                    if ($cellI18n->getLocale() === $locale) {
                        $cellLocale = $cellI18n;
                    }
                }

                $results['body']["row-" . $cell->getPosition()][] = array(
                    'cell' => $cell,
                    'i18n' => $cellLocale,
                    'col' => $col,
                );

                ksort($results['body']);
            }
        }

        foreach ($results as $result) {
            ksort($result);
        }

        return $results;
    }
}