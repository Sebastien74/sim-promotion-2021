<?php

namespace App\Entity\Module\Form;

use App\Entity\Layout\FieldConfiguration;
use App\Repository\Module\Form\ContactValueRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * ContactValue
 *
 * @ORM\Table(name="module_form_contact_value")
 * @ORM\Entity(repositoryClass=ContactValueRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ContactValue
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=500, nullable=true)
     */
    private $label;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Module\Form\ContactForm", inversedBy="contactValues", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=true)
     */
    private $contactForm;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Module\Form\ContactStepForm", inversedBy="contactValues", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=true)
     */
    private $contactStepForm;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Layout\FieldConfiguration", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $configuration;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getContactForm(): ?ContactForm
    {
        return $this->contactForm;
    }

    public function setContactForm(?ContactForm $contactForm): self
    {
        $this->contactForm = $contactForm;

        return $this;
    }

    public function getContactStepForm(): ?ContactStepForm
    {
        return $this->contactStepForm;
    }

    public function setContactStepForm(?ContactStepForm $contactStepForm): self
    {
        $this->contactStepForm = $contactStepForm;

        return $this;
    }

    public function getConfiguration(): ?FieldConfiguration
    {
        return $this->configuration;
    }

    public function setConfiguration(?FieldConfiguration $configuration): self
    {
        $this->configuration = $configuration;

        return $this;
    }
}
