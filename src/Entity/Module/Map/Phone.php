<?php

namespace App\Entity\Module\Map;

use App\Repository\Module\Map\PhoneRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Phone
 *
 * @ORM\Table(name="module_map_phone")
 * @ORM\Entity(repositoryClass=PhoneRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Phone
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $number;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tagNumber;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(?string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getTagNumber(): ?string
    {
        return $this->tagNumber;
    }

    public function setTagNumber(?string $tagNumber): self
    {
        $this->tagNumber = $tagNumber;

        return $this;
    }
}
