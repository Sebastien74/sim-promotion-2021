<?php

namespace App\Entity\Module\Agenda;

use App\Entity\BaseEntity;
use App\Repository\Module\Agenda\PeriodRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Period
 *
 * @ORM\Table(name="module_agenda_period")
 * @ORM\Entity(repositoryClass=PeriodRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Period extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'agenda';
    protected static $interface = [
        'name' => 'agendaperiod'
    ];

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $publicationStart;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $publicationEnd;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Module\Agenda\Information", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $information;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Module\Agenda\Agenda", inversedBy="periods", fetch="EXTRA_LAZY")
     */
    private $agenda;

    public function getPublicationStart(): ?\DateTimeInterface
    {
        return $this->publicationStart;
    }

    public function setPublicationStart(?\DateTimeInterface $publicationStart): self
    {
        $this->publicationStart = $publicationStart;

        return $this;
    }

    public function getPublicationEnd(): ?\DateTimeInterface
    {
        return $this->publicationEnd;
    }

    public function setPublicationEnd(?\DateTimeInterface $publicationEnd): self
    {
        $this->publicationEnd = $publicationEnd;

        return $this;
    }

    public function getInformation(): ?Information
    {
        return $this->information;
    }

    public function setInformation(?Information $information): self
    {
        $this->information = $information;

        return $this;
    }

    public function getAgenda(): ?Agenda
    {
        return $this->agenda;
    }

    public function setAgenda(?Agenda $agenda): self
    {
        $this->agenda = $agenda;

        return $this;
    }
}
