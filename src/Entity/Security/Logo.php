<?php

namespace App\Entity\Security;

use App\Entity\BaseEntity;
use App\Repository\Security\LogoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Logo
 *
 * @ORM\Table(name="security_compagny_logo")
 * @ORM\Entity(repositoryClass=LogoRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Logo extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'customer';
    protected static $interface = [
        'name' => 'logo'
    ];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $filename;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $dirname;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Security\Company", inversedBy="logo", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $company;

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

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(Company $company): self
    {
        $this->company = $company;
        return $this;
    }
}