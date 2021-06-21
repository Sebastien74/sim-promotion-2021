<?php

namespace App\Service\DataFixtures;

use App\Entity\Core\Color;
use App\Entity\Core\Configuration;
use App\Entity\Core\Transition;
use App\Entity\Layout\BlockType;
use App\Entity\Security\User;
use App\Repository\Core\TransitionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * TransitionFixture
 *
 * Transition Fixture management
 *
 * @property array AOS_TRANSITIONS
 *
 * @property EntityManagerInterface $entityManager
 * @property TranslatorInterface $translator
 * @property User $user
 * @property TransitionRepository $repository
 * @property int $position
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TransitionFixture
{
    const BLOCKS_TYPES_CATEGORIES = ['global', 'content'];
    const BLOCKS_TYPES_TRANSITIONS = [
        'title' => [
            'title' => ['lib' => 'aos', 'effect' => 'fade-right', 'delay' => 250],
            'sub-title' => ['lib' => 'aos', 'effect' => 'fade-right', 'delay' => 400]
        ],
        'titleheader' => [
            'title' => ['lib' => 'aos', 'effect' => 'fade-right', 'delay' => 250],
            'sub-title' => ['lib' => 'aos', 'effect' => 'fade-right', 'delay' => 400]
        ]
    ];
    private const ACTIVES_TRANSITIONS = [
        'aos-fade-up',
        'aos-fade-down',
        'aos-fade-right',
        'aos-fade-left',
    ];
    private const AOS_TRANSITIONS = [
        'fade-up' => 'fade-up',
        'fade-down' => 'fade-down',
        'fade-right' => 'fade-right',
        'fade-left' => 'fade-left',
        'fade-up-right' => 'fade-up-right',
        'fade-up-left' => 'fade-up-left',
        'fade-down-right' => 'fade-down-right',
        'fade-down-left' => 'fade-down-left',
        'flip-left' => 'flip-left',
        'flip-right' => 'flip-right',
        'flip-up' => 'flip-up',
        'flip-down' => 'flip-down',
        'zoom-in' => 'zoom-in',
        'zoom-in-up' => 'zoom-in-up',
        'zoom-in-down' => 'zoom-in-down',
        'zoom-in-left' => 'zoom-in-left',
        'zoom-in-right' => 'zoom-in-right',
        'zoom-out' => 'zoom-out',
        'zoom-out-up' => 'zoom-out-up',
        'zoom-out-down' => 'zoom-out-down',
        'zoom-out-right' => 'zoom-out-right',
        'zoom-out-left' => 'zoom-out-left'
    ];

    private $entityManager;
    private $translator;
    private $user;
    private $repository;
    private $position;

    /**
     * TransitionFixture constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param TranslatorInterface $translator
     */
    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    /**
     * Add Transitions
     *
     * @param Configuration $configuration
     * @param User|null $user
     */
    public function add(Configuration $configuration, User $user = NULL)
    {
        $this->user = $user;
        $this->repository = $this->entityManager->getRepository(Transition::class);
        $this->position = count($this->repository->findBy(['configuration' => $configuration])) + 1;

        $this->generateAos($configuration);
        $this->generateBlocksTypes($configuration);
    }

    /**
     * Generate AOS transitions
     *
     * @param Configuration $configuration
     */
    private function generateAos(Configuration $configuration)
    {
        foreach (self::AOS_TRANSITIONS as $name => $aosTransition) {

            if (!$this->existing($configuration, $aosTransition)) {

                $transition = new Transition();
                $transition->setAdminName($name);
                $transition->setSlug($aosTransition);
                $transition->setAosEffect($aosTransition);
                $transition->setIsActive(in_array('aos-' . $aosTransition, self::ACTIVES_TRANSITIONS));
                $transition->setConfiguration($configuration);
                $transition->setPosition($this->position);

                if ($this->user) {
                    $transition->setCreatedBy($this->user);
                }

                $this->entityManager->persist($transition);

                $this->position++;
            }
        }
    }

    /**
     * Generate BlockType transitions
     *
     * @param Configuration $configuration
     */
    private function generateBlocksTypes(Configuration $configuration)
    {
        $blockTypes = $this->entityManager->getRepository(BlockType::class)->findAll();

        foreach ($blockTypes as $blockType) {

            /** @var string $slug */
            $slug = $blockType->getSlug();

            if (in_array($blockType->getCategory(), self::BLOCKS_TYPES_CATEGORIES)
                && isset(self::BLOCKS_TYPES_TRANSITIONS[$slug])
                && is_array(self::BLOCKS_TYPES_TRANSITIONS[$slug])) {

                foreach (self::BLOCKS_TYPES_TRANSITIONS[$slug] as $element => $config) {

                    $transitionSlug = 'block-' . $slug . '-' . $element;

                    if (!$this->existing($configuration, $transitionSlug)) {

                        $library = !empty($config['lib']) ? $config['lib'] : NULL;
                        $effect = !empty($config['effect']) ? $config['effect'] : NULL;
                        $delay = !empty($config['delay']) ? $config['delay'] : NULL;

                        $transition = new Transition();
                        $transition->setAdminName($blockType->getAdminName() . ' ' . $element);
                        $transition->setSlug($transitionSlug);
                        $transition->setSection('block-' . $slug);
                        $transition->setElement($element);
                        $transition->setIsActive(true);
                        $transition->setDelay($delay);
                        $transition->setPosition($this->position);
                        $transition->setConfiguration($configuration);

                        if ($library === 'aos') {
                            $transition->setAosEffect($effect);
                        } elseif ($library === 'lax') {
                            $transition->setLaxPreset($effect);
                        }

                        if ($this->user) {
                            $transition->setCreatedBy($this->user);
                        }

                        $this->entityManager->persist($transition);
                        $this->entityManager->flush();

                        $this->position++;
                    }
                }
            }
        }
    }

    /**
     * Check if Transition already exist
     *
     * @param Configuration $configuration
     * @param string $slug
     * @return Transition[]
     */
    private function existing(Configuration $configuration, string $slug): array
    {
        return $this->repository->findBy([
            'configuration' => $configuration,
            'slug' => $slug
        ]);
    }
}