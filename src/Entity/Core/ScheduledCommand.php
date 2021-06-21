<?php

namespace App\Entity\Core;

use App\Entity\BaseEntity;
use App\Repository\Core\ScheduledCommandRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ScheduledCommand
 *
 * @ORM\Table(name="core_scheduled_command")
 * @ORM\Entity(repositoryClass=ScheduledCommandRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ScheduledCommand extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'website';
    protected static $interface = [
        'name' => 'command'
    ];

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $command;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $arguments;

    /**
     * @see http://www.abunchofutils.com/utils/developer/cron-expression-helper/
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $cronExpression;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastExecution;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $lastReturnCode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $logFile;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $priority = 0;

    /**
     * If true, command will be execute next time regardless cron expression
     *
     * @ORM\Column(type="boolean")
     */
    private $executeImmediately = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $locked = false;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Website", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $website;

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        if (!$this->getLogFile()) {
            $this->setLogFile(Urlizer::urlize($this->getCommand()) . '.log');
        }

        $this->setLastExecution(new \DateTime());
        $this->setLocked(false);
    }

    public function getCommand(): ?string
    {
        return $this->command;
    }

    public function setCommand(string $command): self
    {
        $this->command = $command;

        return $this;
    }

    public function getArguments(): ?string
    {
        return $this->arguments;
    }

    public function setArguments(string $arguments): self
    {
        $this->arguments = $arguments;

        return $this;
    }

    public function getCronExpression(): ?string
    {
        return $this->cronExpression;
    }

    public function setCronExpression(string $cronExpression): self
    {
        $this->cronExpression = $cronExpression;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getLastExecution(): ?\DateTimeInterface
    {
        return $this->lastExecution;
    }

    public function setLastExecution(?\DateTimeInterface $lastExecution): self
    {
        $this->lastExecution = $lastExecution;

        return $this;
    }

    public function getLastReturnCode(): ?int
    {
        return $this->lastReturnCode;
    }

    public function setLastReturnCode(?int $lastReturnCode): self
    {
        $this->lastReturnCode = $lastReturnCode;

        return $this;
    }

    public function getLogFile(): ?string
    {
        return $this->logFile;
    }

    public function setLogFile(string $logFile): self
    {
        $this->logFile = $logFile;

        return $this;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(?int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function isExecuteImmediately(): ?bool
    {
        return $this->executeImmediately;
    }

    public function getExecuteImmediately(): ?bool
    {
        return $this->executeImmediately;
    }

    public function setExecuteImmediately(bool $executeImmediately): self
    {
        $this->executeImmediately = $executeImmediately;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function isLocked(): ?bool
    {
        return $this->locked;
    }

    public function getLocked(): ?bool
    {
        return $this->locked;
    }

    public function setLocked(bool $locked): self
    {
        $this->locked = $locked;

        return $this;
    }

    public function getWebsite(): ?Website
    {
        return $this->website;
    }

    public function setWebsite(?Website $website): self
    {
        $this->website = $website;

        return $this;
    }
}
