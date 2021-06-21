<?php

namespace App\Entity\Todo;

use App\Entity\BaseEntity;
use App\Repository\Todo\TaskRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Task
 *
 * @ORM\Table(name="todo_task")
 * @ORM\Entity(repositoryClass=TaskRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Task extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'todo';
    protected static $interface = [
        'name' => 'task'
    ];

    /**
     * @ORM\Column(type="boolean")
     */
    private $done = false;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Todo\Todo", inversedBy="tasks", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private $todo;

    public function getDone(): ?bool
    {
        return $this->done;
    }

    public function setDone(bool $done): self
    {
        $this->done = $done;

        return $this;
    }

    public function getTodo(): ?Todo
    {
        return $this->todo;
    }

    public function setTodo(?Todo $todo): self
    {
        $this->todo = $todo;

        return $this;
    }
}
