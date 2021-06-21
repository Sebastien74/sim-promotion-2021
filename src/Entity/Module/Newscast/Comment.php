<?php

namespace App\Entity\Module\Newscast;

use App\Entity\BaseEntity;
use App\Repository\Module\Newscast\CommentRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Comment
 *
 * @ORM\Table(name="module_newscast_comment")
 * @ORM\Entity(repositoryClass=CommentRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Comment extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'newscast';
    protected static $interface = [
        'name' => 'newscastcomment'
    ];

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $authorName;

    /**
     * @ORM\Column(type="text")
     */
    private $message;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Module\Newscast\Newscast", inversedBy="comments", fetch="EXTRA_LAZY")
     */
    private $newscast;

    public function getAuthorName(): ?string
    {
        return $this->authorName;
    }

    public function setAuthorName(string $authorName): self
    {
        $this->authorName = $authorName;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getNewscast(): ?Newscast
    {
        return $this->newscast;
    }

    public function setNewscast(?Newscast $newscast): self
    {
        $this->newscast = $newscast;

        return $this;
    }
}
