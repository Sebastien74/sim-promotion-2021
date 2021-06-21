<?php

namespace App\Service\Development\Zend;

use App\Entity\Core\Website;
use App\Entity\Security\Group;
use Doctrine\DBAL\DBALException;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * ZendImport
 *
 * @property UserBackImport $userBackImport
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ZendImport
{
    private $userBackImport;

    /**
     * ZendImport constructor.
     *
     * @param UserBackImport $userBackImport
     */
    public function __construct(UserBackImport $userBackImport)
    {
        $this->userBackImport = $userBackImport;
    }

    /**
     * Import Users Back
     *
     * @param Website $website
     * @param Group|null $group
     * @param SymfonyStyle $io
     * @throws DBALException
     */
    public function usersBack(Website $website, Group $group = NULL, SymfonyStyle $io = NULL)
    {
        $this->userBackImport->import($website, $group, $io);
    }
}