<?php

namespace App\Service\Development;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;

/**
 * SqlService
 *
 * To get SQL data from current connection
 *
 * @property Connection $connection
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SqlService
{
    private $connection;

    /**
     * SqlService constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->connection = $entityManager->getConnection();
    }

    /**
     * Find all in table
     *
     * @param string $table
     * @return array
     * @throws DBALException
     */
    public function findAll(string $table)
    {
        $schemaManager = $this->connection->getSchemaManager();

        if ($schemaManager->tablesExist(array($table)) == true) {
            $statement = $this->connection->prepare("SELECT * FROM " . $table);
            $statement->execute();
            return $statement->fetchAll();
        }

        return [];
    }

    /**
     * Find all in table
     *
     * @param string $table
     * @param string $column
     * @param int|string $value
     * @return array
     * @throws DBALException
     */
    public function findBy(string $table, string $column, $value)
    {
        $schemaManager = $this->connection->getSchemaManager();

        if ($schemaManager->tablesExist(array($table)) == true) {
            $statement = $this->connection->prepare("SELECT * FROM " . $table . " WHERE " . $column . " = " . $value);
            $statement->execute();
            return $statement->fetchAll();
        }

        return [];
    }
}