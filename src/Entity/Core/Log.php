<?php

namespace App\Entity\Core;

use App\Entity\BaseEntity;
use App\Repository\Core\LogRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Log
 *
 * @ORM\Table(name="core_log")
 * @ORM\Entity(repositoryClass=LogRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Log extends BaseEntity
{
    /**
     * @ORM\Column(type="boolean")
     */
    private $isRead = false;

    public function getIsRead(): ?bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): self
    {
        $this->isRead = $isRead;

        return $this;
    }

}