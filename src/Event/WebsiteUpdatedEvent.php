<?php

namespace App\Event;

use App\Entity\Core\Website;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * WebsiteUpdatedEvent
 *
 * @property string NAME
 * @property Website $website
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class WebsiteUpdatedEvent extends Event
{
    public const NAME = 'website.updated';

    protected $website;

    /**
     * WebsiteUpdatedEvent constructor.
     *
     * @param Website $website
     */
    public function __construct(Website $website)
    {
        $this->website = $website;
    }

    /**
     * Get Website
     *
     * @return Website
     */
    public function getWebsite()
    {
        return $this->website;
    }
}