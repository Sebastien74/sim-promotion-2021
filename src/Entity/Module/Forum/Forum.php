<?php

namespace App\Entity\Module\Forum;

use App\Entity\BaseEntity;
use App\Entity\Core\Website;
use App\Repository\Module\Forum\ForumRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Forum
 *
 * @ORM\Table(name="module_forum")
 * @ORM\Entity(repositoryClass=ForumRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 * @property array $labels
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Forum extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'website';
    protected static $interface = [
        'name' => 'forum',
        'buttons' => [
            'admin_forumcomment_index'
        ]
    ];
    protected static $labels = [
        "admin_forumcomment_index" => "Commentaires"
    ];

    /**
     * @ORM\Column(type="boolean")
     */
    private $moderation = true;

    /**
     * @ORM\Column(type="boolean")
     */
    private $login = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $recaptcha = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $securityKey;

    /**
     * @ORM\Column(type="boolean")
     */
    private $hideDate = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $formatDate = "cccc dd MMMM Y à HH:mm";

    /**
     * @ORM\Column(type="array")
     */
    private $fields = ['authorName', 'message'];

    /**
     * @ORM\Column(type="array")
     */
    private $requireFields = ['authorName', 'message'];

    /**
     * @ORM\Column(type="array")
     */
    private $widgets = ['comments', 'likes', 'shares'];

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Module\Forum\Comment", mappedBy="forum", cascade={"persist"}, fetch="EXTRA_LAZY", orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @ORM\OrderBy({"createdAt"="DESC"})
     * @Assert\Valid()
     */
    private $comments;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Website")
     * @ORM\JoinColumn(nullable=false)
     */
    private $website;

    /**
     * Forum constructor.
     */
    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

    public function getModeration(): ?bool
    {
        return $this->moderation;
    }

    public function setModeration(bool $moderation): self
    {
        $this->moderation = $moderation;

        return $this;
    }

    public function getLogin(): ?bool
    {
        return $this->login;
    }

    public function setLogin(bool $login): self
    {
        $this->login = $login;

        return $this;
    }

    public function getRecaptcha(): ?bool
    {
        return $this->recaptcha;
    }

    public function setRecaptcha(bool $recaptcha): self
    {
        $this->recaptcha = $recaptcha;

        return $this;
    }

    public function getSecurityKey(): ?string
    {
        return $this->securityKey;
    }

    public function setSecurityKey(?string $securityKey): self
    {
        $this->securityKey = $securityKey;

        return $this;
    }

    public function getHideDate(): ?bool
    {
        return $this->hideDate;
    }

    public function setHideDate(bool $hideDate): self
    {
        $this->hideDate = $hideDate;

        return $this;
    }

    public function getFormatDate(): ?string
    {
        return $this->formatDate;
    }

    public function setFormatDate(?string $formatDate): self
    {
        $this->formatDate = $formatDate;

        return $this;
    }

    public function getFields(): ?array
    {
        return $this->fields;
    }

    public function setFields(?array $fields): self
    {
        $this->fields = $fields;

        return $this;
    }

    public function getRequireFields(): ?array
    {
        return $this->requireFields;
    }

    public function setRequireFields(?array $requireFields): self
    {
        $this->requireFields = $requireFields;

        return $this;
    }

    public function getWidgets(): ?array
    {
        return $this->widgets;
    }

    public function setWidgets(?array $widgets): self
    {
        $this->widgets = $widgets;

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setForum($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getForum() === $this) {
                $comment->setForum(null);
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
}
