<?php

namespace App\Entity\Gdpr;

use App\Entity\BaseEntity;
use App\Repository\Gdpr\CookieRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Cookie
 *
 * @ORM\Table(name="gdpr_cookie")
 * @ORM\Entity(repositoryClass=CookieRepository::class)
 * @ORM\HasLifecycleCallbacks
 *
 * @property string $masterField
 * @property array $interface
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Cookie extends BaseEntity
{
    /**
     * Configurations
     */
    protected static $masterField = 'gdprgroup';
    protected static $interface = [
        'name' => 'gdprcookie'
    ];

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $code;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Gdpr\Group", inversedBy="gdprcookies", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $gdprgroup;

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getGdprgroup(): ?Group
    {
        return $this->gdprgroup;
    }

    public function setGdprgroup(?Group $gdprgroup): self
    {
        $this->gdprgroup = $gdprgroup;

        return $this;
    }
}
