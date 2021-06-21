<?php

namespace App\Entity\Layout;

use App\Repository\Layout\ActionI18nRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * ActionI18n
 *
 * @ORM\Table(name="layout_action_i18n")
 * @ORM\Entity(repositoryClass=ActionI18nRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ActionI18n
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $locale;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $actionFilter;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Layout\Block", inversedBy="actionI18ns", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $block;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function getActionFilter(): ?int
    {
        return $this->actionFilter;
    }

    public function setActionFilter(?int $actionFilter): self
    {
        $this->actionFilter = $actionFilter;

        return $this;
    }

    public function getBlock(): ?Block
    {
        return $this->block;
    }

    public function setBlock(?Block $block): self
    {
        $this->block = $block;

        return $this;
    }
}
