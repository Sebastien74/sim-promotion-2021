<?php

namespace App\Entity\Api;

use App\Repository\Api\InstagramRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Instagram
 *
 * @ORM\Table(name="api_instagram")
 * @ORM\Entity(repositoryClass=InstagramRepository::class)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Instagram
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $accessToken;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\NotBlank()
     */
    private $nbrItems = 7;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Api\Api", inversedBy="instagram", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\Valid()
     */
    private $api;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(?string $accessToken): self
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function getNbrItems(): ?int
    {
        return $this->nbrItems;
    }

    public function setNbrItems(?int $nbrItems): self
    {
        $this->nbrItems = $nbrItems;

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