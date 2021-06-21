<?php

namespace App\Service\DataFixtures;

use App\Entity\Core\Configuration;
use App\Entity\Layout\BlockType;
use Doctrine\ORM\EntityManagerInterface;

/**
 * BlockTypeFixture
 *
 * BlockType Fixture management
 *
 * @property array DISABLED
 *
 * @property EntityManagerInterface $entityManager
 * @property BlockType[] $blockTypes
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class BlockTypeFixture
{
    private const DISABLED = [
        'action',
        'alert',
        'blockquote',
        'card',
        'collapse',
        'icon',
        'modal',
        'share',
        'counter',
    ];

    private $entityManager;
    private $blockTypes;

    /**
     * BlockTypeFixture constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->blockTypes = $this->entityManager->getRepository(BlockType::class)->findAll();
    }

    /**
     * Add BlockType[]
     *
     * @param Configuration $configuration
     * @param bool $devMode
     */
    public function add(Configuration $configuration, bool $devMode)
    {
        foreach ($this->blockTypes as $blockType) {
            if ($devMode || !in_array($blockType->getSlug(), self::DISABLED)) {
                $configuration->addBlockType($blockType);
            }
        }

        $this->entityManager->persist($configuration);
    }
}