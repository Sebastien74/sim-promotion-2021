<?php

namespace App\Entity\Seo;

use App\Entity\BaseInterface;
use App\Entity\Core\Website;
use App\Repository\Seo\RedirectionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Redirection
 *
 * @ORM\Table(name="seo_redirection")
 * @ORM\Entity(repositoryClass=RedirectionRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @UniqueEntity(fields = {"old", "locale", "website"})
 *
 * @property string $masterField
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Redirection extends BaseInterface
{
    /**
     * Configurations
     */
    protected static $masterField = 'website';
    protected static $interface = [
        'name' => 'redirection'
    ];

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
     * @ORM\Column(type="string", length=255)
     */
    private $old;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $new;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Website", inversedBy="redirections", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $website;

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

    public function getOld(): ?string
    {
        return $this->old;
    }

    public function setOld(string $old): self
    {
        $this->old = $old;

        return $this;
    }

    public function getNew(): ?string
    {
        return $this->new;
    }

    public function setNew(string $new): self
    {
        $this->new = $new;

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
