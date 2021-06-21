<?php

namespace App\Entity\Module\Newsletter;

use App\Entity\BaseInterface;
use App\Repository\Module\Newsletter\EmailRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Email
 *
 * @ORM\Table(name="module_newsletter_email")
 * @ORM\Entity(repositoryClass=EmailRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Email extends BaseInterface
{
    /**
     * Configurations
     */
    protected static $masterField = 'campaign';
    protected static $interface = [
        'name' => 'newsletteremail'
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
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $locale;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Module\Newsletter\Campaign", inversedBy="emails", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $campaign;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
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

    public function getCampaign(): ?Campaign
    {
        return $this->campaign;
    }

    public function setCampaign(?Campaign $campaign): self
    {
        $this->campaign = $campaign;

        return $this;
    }
}
