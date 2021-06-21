<?php

namespace App\Entity\Module\Form;

use App\Entity\BaseEntity;
use App\Entity\Core\Website;
use App\Entity\Translation\i18n;
use App\Repository\Module\Form\StepFormRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * StepForm
 *
 * @ORM\Table(name="module_form_step")
 * @ORM\Entity(repositoryClass=StepFormRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class StepForm extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'website';
    protected static $interface = [
        'name' => 'stepform',
        'buttons' => [
            'admin_form_index',
            'admin_contactstepform_index',
        ]
    ];
    protected static $labels = [
        "admin_form_index" => "Formulaires",
        "admin_contactstepform_index" => "Contacts"
    ];

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Module\Form\Configuration", mappedBy="stepform", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE")
     */
    private $configuration;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Module\Form\Form", mappedBy="stepform", orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"position"="ASC"})
     */
    private $forms;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Module\Form\ContactStepForm", mappedBy="stepform", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     */
    private $contacts;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Website", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $website;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Translation\i18n", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"locale"="ASC"})
     * @ORM\JoinTable(name="module_form_step_i18ns",
     *      joinColumns={@ORM\JoinColumn(name="form_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="i18n_id", referencedColumnName="id", onDelete="cascade", unique=true)}
     * )
     * @Assert\Valid()
     */
    private $i18ns;

    /**
     * StepForm constructor.
     */
    public function __construct()
    {
        $this->forms = new ArrayCollection();
        $this->contacts = new ArrayCollection();
        $this->i18ns = new ArrayCollection();
    }

    public function getConfiguration(): ?Configuration
    {
        return $this->configuration;
    }

    public function setConfiguration(?Configuration $configuration): self
    {
        $this->configuration = $configuration;

        return $this;
    }

    /**
     * @return Collection|Form[]
     */
    public function getForms(): Collection
    {
        return $this->forms;
    }

    public function addForm(Form $form): self
    {
        if (!$this->forms->contains($form)) {
            $this->forms[] = $form;
            $form->setStepform($this);
        }

        return $this;
    }

    public function removeForm(Form $form): self
    {
        if ($this->forms->contains($form)) {
            $this->forms->removeElement($form);
            // set the owning side to null (unless already changed)
            if ($form->getStepform() === $this) {
                $form->setStepform(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ContactStepForm[]
     */
    public function getContacts(): Collection
    {
        return $this->contacts;
    }

    public function addContact(ContactStepForm $contact): self
    {
        if (!$this->contacts->contains($contact)) {
            $this->contacts[] = $contact;
            $contact->setStepform($this);
        }

        return $this;
    }

    public function removeContact(ContactStepForm $contact): self
    {
        if ($this->contacts->contains($contact)) {
            $this->contacts->removeElement($contact);
            // set the owning side to null (unless already changed)
            if ($contact->getStepform() === $this) {
                $contact->setStepform(null);
            }
        }

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

    /**
     * @return Collection|i18n[]
     */
    public function getI18ns(): Collection
    {
        return $this->i18ns;
    }

    public function addI18n(i18n $i18n): self
    {
        if (!$this->i18ns->contains($i18n)) {
            $this->i18ns[] = $i18n;
        }

        return $this;
    }

    public function removeI18n(i18n $i18n): self
    {
        if ($this->i18ns->contains($i18n)) {
            $this->i18ns->removeElement($i18n);
        }

        return $this;
    }
}
