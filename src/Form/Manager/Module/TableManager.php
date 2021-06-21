<?php

namespace App\Form\Manager\Module;

use App\Entity\Module\Table\Cell;
use App\Entity\Module\Table\Col;
use App\Entity\Module\Table\Table;
use App\Entity\Core\Website;
use Doctrine\ORM\EntityManagerInterface;

/**
 * TableManager
 *
 * Manage admin Table form
 *
 * @property EntityManagerInterface $entityManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TableManager
{
    private $entityManager;

    /**
     * TableManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @prePersist
     *
     * @param Table $table
     * @param Website $website
     */
    public function prePersist(Table $table, Website $website)
    {
        $this->setStart($table, $website);
    }

    private function setStart(Table $table, Website $website)
    {
        $col = new Col();
        $table->addCol($col);

        $header = new Cell();
        $col->addCell($header);

        $this->entityManager->persist($table);
        $this->entityManager->flush();
    }

    /**
     * Add Col in Table
     *
     * @param Table $table
     * @param Website $website
     */
    public function addCol(Table $table, Website $website)
    {
        $col = new Col();
        $col->setPosition(count($table->getCols()) + 1);
        $table->addCol($col);

        if ($table->getCols()->isEmpty()) {
            $newCell = new Cell();
            $col->addCell($newCell);
        } else {
            foreach ($table->getCols()[0]->getCells() as $cell) {
                $newCell = new Cell();
                $newCell->setPosition($cell->getPosition());
                $col->addCell($newCell);
            }
        }

        $this->entityManager->persist($table);
        $this->entityManager->flush();
    }

    /**
     * Set Col position
     *
     * @param Table $table
     * @param Col $col
     * @param string $type
     */
    public function colPosition(Table $table, Col $col, string $type)
    {
        $colSetPosition = $col->getPosition();
        $newPosition = $type === "down" ? $colSetPosition - 1 : $colSetPosition + 1;
        $col->setPosition($newPosition);

        $this->entityManager->persist($col);

        foreach ($table->getCols() as $colDb) {
            if ($colDb->getId() != $col->getId()) {
                if ($colDb->getPosition() == $newPosition && $type === "down") {
                    $colDb->setPosition($colDb->getPosition() + 1);
                    $this->entityManager->persist($colDb);
                } elseif ($colDb->getPosition() == $newPosition && $type === "up") {
                    $colDb->setPosition($colDb->getPosition() - 1);
                    $this->entityManager->persist($colDb);
                }
            }
        }

        $this->entityManager->flush();
    }

    /**
     * Delete Col[] Table
     *
     * @param Table $table
     * @param Col $col
     */
    public function deleteCol(Table $table, Col $col)
    {
        $colDeletedPosition = $col->getPosition();
        $this->entityManager->remove($col);

        foreach ($table->getCols() as $colDb) {
            if ($colDb->getPosition() > $colDeletedPosition) {
                $colDb->setPosition($colDb->getPosition() - 1);
                $this->entityManager->persist($colDb);
            }
        }

        $this->entityManager->flush();
    }

    /**
     * Add Cell[] row in Table
     *
     * @param Table $table
     * @param Website $website
     */
    public function addRow(Table $table, Website $website)
    {
        if ($table->getCols()->isEmpty()) {
            $this->setStart($table, $website);
        } else {

            foreach ($table->getCols() as $col) {
                $newCell = new Cell();
                $newCell->setPosition(count($col->getCells()) + 1);
                $col->addCell($newCell);
            }
        }

        $this->entityManager->persist($table);
        $this->entityManager->flush();
    }

    /**
     * Set Cell[] row position
     *
     * @param Table $table
     * @param int $position
     * @param string $type
     */
    public function rowPosition(Table $table, int $position, string $type)
    {
        $newPosition = $type === "up" ? $position - 1 : $position + 1;

        foreach ($table->getCols() as $colDb) {
            foreach ($colDb->getCells() as $cell) {
                if ($cell->getPosition() === $position) {
                    $cell->setPosition($newPosition);
                    $this->entityManager->persist($cell);
                } elseif ($cell->getPosition() === $newPosition && $type === "up") {
                    $cell->setPosition($cell->getPosition() + 1);
                    $this->entityManager->persist($cell);
                } elseif ($cell->getPosition() === $newPosition && $type === "down") {
                    $cell->setPosition($cell->getPosition() - 1);
                    $this->entityManager->persist($cell);
                }
            }
        }

        $this->entityManager->flush();
    }

    /**
     * Delete Cell[] row
     *
     * @param Table $table
     * @param int $position
     */
    public function deleteRow(Table $table, int $position)
    {
        foreach ($table->getCols() as $colDb) {
            foreach ($colDb->getCells() as $cell) {
                if ($cell->getPosition() === $position) {
                    $this->entityManager->remove($cell);
                }
            }
        }

        $this->entityManager->flush();

        foreach ($table->getCols() as $colDb) {
            foreach ($colDb->getCells() as $cell) {
                if ($cell->getPosition() > $position) {
                    $cell->setPosition($cell->getPosition() - 1);
                    $this->entityManager->persist($cell);
                }
            }
        }

        $this->entityManager->flush();
    }
}