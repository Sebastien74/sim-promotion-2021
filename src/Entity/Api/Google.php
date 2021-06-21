<?php

namespace App\Entity\Api;

use App\Repository\Api\GoogleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Google
 *
 * @ORM\Table(name="api_google")
 * @ORM\Entity(repositoryClass=GoogleRepository::class)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Google
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Api\Api", inversedBy="google", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $api;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Api\GoogleI18n", mappedBy="google", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @ORM\OrderBy({"locale"="ASC"})
     * @Assert\Valid()
     */
    private $googleI18ns;

    /**
     * Google constructor.
     */
    public function __construct()
    {
        $this->googleI18ns = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection|GoogleI18n[]
     */
    public function getGoogleI18ns(): Collection
    {
        return $this->googleI18ns;
    }

    public function addGoogleI18n(GoogleI18n $googleI18n): self
    {
        if (!$this->googleI18ns->contains($googleI18n)) {
            $this->googleI18ns[] = $googleI18n;
            $googleI18n->setGoogle($this);
        }

        return $this;
    }

    public function removeGoogleI18n(GoogleI18n $googleI18n): self
    {
        if ($this->googleI18ns->contains($googleI18n)) {
            $this->googleI18ns->removeElement($googleI18n);
            // set the owning side to null (unless already changed)
            if ($googleI18n->getGoogle() === $this) {
                $googleI18n->setGoogle(null);
            }
        }

        return $this;
    }
}
