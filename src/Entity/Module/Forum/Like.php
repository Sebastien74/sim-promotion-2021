<?php

namespace App\Entity\Module\Forum;

use App\Entity\BaseEntity;
use App\Repository\Module\Forum\LikeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Like
 *
 * @ORM\Table(name="module_forum_comment_like")
 * @ORM\Entity(repositoryClass=LikeRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Like extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'comment';
    protected static $interface = [
        'name' => 'forumcommentlike'
    ];

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Module\Forum\Comment", inversedBy="likes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $comment;

    public function getComment(): ?Comment
    {
        return $this->comment;
    }

    public function setComment(?Comment $comment): self
    {
        $this->comment = $comment;

        return $this;
    }
}
