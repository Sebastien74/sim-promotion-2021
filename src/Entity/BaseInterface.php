<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * BaseInterface
 *
 * @property string $masterField
 * @property string $parentMasterField
 * @property array $interface
 * @property array $labels
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class BaseInterface extends BaseUserAction
{
    /**
     * Configurations
     */
    protected static $masterField = '';
    protected static $parentMasterField = '';
    protected static $interface = [];
    protected static $labels = [];

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="create")
     * @var DateTime
     */
    protected $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="update")
     * @var DateTime
     */
    protected $updatedAt;

    /**
     * Get MasterField
     *
     * @return string
     */
    public static function getMasterField()
    {
        return static::$masterField;
    }

    /**
     * Get Parent MasterField
     *
     * @return string
     */
    public static function getParentMasterField()
    {
        return static::$parentMasterField;
    }

    /**
     * Get interface
     *
     * @return array
     */
    public static function getInterface()
    {
        return static::$interface;
    }

    /**
     * Get labels
     *
     * @return array
     */
    public static function getLabels()
    {
        return static::$labels;
    }

    /**
     * Sets createdAt.
     *
     * @param DateTime $createdAt
     * @return $this
     */
    public function setCreatedAt(DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Returns createdAt.
     *
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Sets updatedAt.
     *
     * @param DateTime $updatedAt
     * @return $this
     */
    public function setUpdatedAt(DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Returns updatedAt.
     *
     * @return DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
}