<?php

namespace App\Entity\Seo;

use App\Entity\Module\Form\Form;
use App\Entity\Module\Form\StepForm;
use App\Entity\BaseEntity;
use App\Entity\Core\Website;
use App\Repository\Seo\FormSuccessRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * FormSuccess
 *
 * @ORM\Table(name="seo_form_success")
 * @ORM\Entity(repositoryClass=FormSuccessRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FormSuccess extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'website';
    protected static $interface = [
        'name' => 'formsuccess'
    ];

    /**
     * @ORM\Column(type="text")
     */
    private $url;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Module\Form\Form", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $form;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Module\Form\StepForm", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=true)
     */
    private $stepForm;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Website", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $website;

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getForm(): ?Form
    {
        return $this->form;
    }

    public function setForm(?Form $form): self
    {
        $this->form = $form;

        return $this;
    }

    public function getStepForm(): ?StepForm
    {
        return $this->stepForm;
    }

    public function setStepForm(?StepForm $stepForm): self
    {
        $this->stepForm = $stepForm;

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
