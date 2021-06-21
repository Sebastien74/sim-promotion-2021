<?php

namespace App\Entity\Security;

use App\Repository\Security\PictureRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Picture
 *
 * @ORM\Table(name="security_user_picture")
 * @ORM\Entity(repositoryClass=PictureRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Picture
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $filename;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $dirname;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Security\User", mappedBy="picture", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Security\UserFront", mappedBy="picture", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $userFront;

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(?string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getDirname(): ?string
    {
        return $this->dirname;
    }

    public function setDirname(?string $dirname): self
    {
        $this->dirname = $dirname;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getUserFront(): ?UserFront
    {
        return $this->userFront;
    }

    public function setUserFront(UserFront $userFront): self
    {
        $this->userFront = $userFront;

        return $this;
    }
}
