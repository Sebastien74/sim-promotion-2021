<?php

namespace App\DataFixtures;

use App\Service\Core\SubscriberService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * BaseFixture
 *
 * Base Fixtures management
 *
 * @property TranslatorInterface $translator
 * @property SubscriberService $subscriber
 * @property string $locale
 * @property ObjectManager $manager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
abstract class BaseFixture extends Fixture
{
    protected $translator;
    protected $subscriber;
    protected $locale;
    protected $manager;

    /**
     * BaseFixture constructor.
     *
     * @param TranslatorInterface $translator
     * @param SubscriberService $subscriber
     */
    public function __construct(TranslatorInterface $translator, SubscriberService $subscriber)
    {
        $this->translator = $translator;
        $this->subscriber = $subscriber;
        $this->locale = $this->translator->getLocale();
    }

    /**
     * Load data
     *
     * @param ObjectManager $manager
     * @return mixed
     */
    abstract protected function loadData(ObjectManager $manager);

    /**
     * Load
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        $this->loadData($manager);
    }

    /**
     * Get icon path
     *
     * @param string $classes
     * @return string
     */
    protected function getIconPath(string $classes): string
    {
        $libraries = [
            'fab' => 'brands',
            'fad' => 'duotone',
            'fal' => 'light',
            'far' => 'regular',
            'fas' => 'solid'
        ];

        $matches = explode(' ', $classes);

        return '/medias/icons/fontawesome/' . $libraries[$matches[0]] . '/' . str_replace('fa-', '', end($matches)) . '.svg';
    }
}