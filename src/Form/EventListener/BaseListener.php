<?php

namespace App\Form\EventListener;

use App\Entity\Core\Website;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * BaseListener
 *
 * @property array $options
 * @property array $labels
 * @property Website $website
 * @property string $defaultLocale
 * @property array $locales
 * @property FormEvent $event
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
abstract class BaseListener implements EventSubscriberInterface
{
    protected $options = [];
    protected $website;
    protected $defaultLocale;
    protected $locales = [];
    protected $event;

    /**
     * BaseListener constructor.
     *
     * @param array $options
     */
    public function __construct($options = [])
    {
        $this->options = $options;
        $session = new Session();
        $this->website = !empty($options['website']) ? $options['website'] : $session->get('adminWebsite');

        if ($this->website) {
            $configuration = $this->website->getConfiguration();
            $this->defaultLocale = $configuration->getLocale();
            $this->locales = $configuration->getAllLocales();
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'onPreSetData'
        ];
    }

    /**
     * preSetData
     *
     * @param FormEvent $event
     */
    abstract protected function preSetData(FormEvent $event);

    /**
     * onPreSetData
     *
     * @param FormEvent $event
     */
    public function onPreSetData(FormEvent $event)
    {
        $this->preSetData($event);
    }
}