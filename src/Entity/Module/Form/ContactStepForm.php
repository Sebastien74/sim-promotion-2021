<?php

namespace App\Entity\Module\Form;

use App\Entity\BaseInterface;
use App\Repository\Module\Form\ContactStepFormRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * ContactStepForm
 *
 * @ORM\Table(name="module_form_step_contact")
 * @ORM\Entity(repositoryClass=ContactStepFormRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ContactStepForm extends BaseInterface
{
    /**
     * Configurations
     */
    protected static $masterField = 'stepform';
    protected static $interface = [
        'name' => 'contactstepform'
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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=500, nullable=true)
     */
    private $token;

    /**
     * @ORM\Column(type="boolean")
     */
    private $tokenExpired = false;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Module\Form\ContactValue", mappedBy="contactStepForm", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     */
    private $contactValues;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Module\Form\StepForm", inversedBy="contacts", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=true)
     */
    private $stepform;

    /**
     * ContactStepForm constructor.
     */
    public function __construct()
    {
        $this->contactValues = new ArrayCollection();
    }

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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getTokenExpired(): ?bool
    {
        return $this->tokenExpired;
    }

    public function setTokenExpired(bool $tokenExpired): self
    {
        $this->tokenExpired = $tokenExpired;

        return $this;
    }

    /**
     * @return Collection|ContactValue[]
     */
    public function getContactValues(): Collection
    {
        return $this->contactValues;
    }

    public function addContactValue(ContactValue $contactValue): self
    {
        if (!$this->contactValues->contains($contactValue)) {
            $this->contactValues[] = $contactValue;
            $contactValue->setContactStepForm($this);
        }

        return $this;
    }

    public function removeContactValue(ContactValue $contactValue): self
    {
        if ($this->contactValues->contains($contactValue)) {
            $this->contactValues->removeElement($contactValue);
            // set the owning side to null (unless already changed)
            if ($contactValue->getContactStepForm() === $this) {
                $contactValue->setContactStepForm(null);
            }
        }

        return $this;
    }

    public function getStepform(): ?StepForm
    {
        return $this->stepform;
    }

    public function setStepform(?StepForm $stepform): self
    {
        $this->stepform = $stepform;

        return $this;
    }
}
