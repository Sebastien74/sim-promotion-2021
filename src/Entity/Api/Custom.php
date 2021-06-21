<?php

namespace App\Entity\Api;

use App\Repository\Api\CustomRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Custom
 *
 * @ORM\Table(name="api_custom")
 * @ORM\Entity(repositoryClass=CustomRepository::class)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Custom
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $headScript;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $topBodyScript;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $bottomBodyScript;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $headScriptSeo;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $topBodyScriptSeo;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $bottomBodyScriptSeo;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Api\Api", inversedBy="custom", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $api;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHeadScript(): ?string
    {
        return $this->headScript;
    }

    public function setHeadScript(?string $headScript): self
    {
        $this->headScript = $headScript;

        return $this;
    }

    public function getTopBodyScript(): ?string
    {
        return $this->topBodyScript;
    }

    public function setTopBodyScript(?string $topBodyScript): self
    {
        $this->topBodyScript = $topBodyScript;

        return $this;
    }

    public function getBottomBodyScript(): ?string
    {
        return $this->bottomBodyScript;
    }

    public function setBottomBodyScript(?string $bottomBodyScript): self
    {
        $this->bottomBodyScript = $bottomBodyScript;

        return $this;
    }

    public function getHeadScriptSeo(): ?string
    {
        return $this->headScriptSeo;
    }

    public function setHeadScriptSeo(?string $headScriptSeo): self
    {
        $this->headScriptSeo = $headScriptSeo;

        return $this;
    }

    public function getTopBodyScriptSeo(): ?string
    {
        return $this->topBodyScriptSeo;
    }

    public function setTopBodyScriptSeo(?string $topBodyScriptSeo): self
    {
        $this->topBodyScriptSeo = $topBodyScriptSeo;

        return $this;
    }

    public function getBottomBodyScriptSeo(): ?string
    {
        return $this->bottomBodyScriptSeo;
    }

    public function setBottomBodyScriptSeo(?string $bottomBodyScriptSeo): self
    {
        $this->bottomBodyScriptSeo = $bottomBodyScriptSeo;

        return $this;
    }

    public function getApi(): ?Api
    {
        return $this->api;
    }

    public function setApi(Api $api): self
    {
        $this->api = $api;

        return $this;
    }
}
