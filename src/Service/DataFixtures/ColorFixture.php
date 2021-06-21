<?php

namespace App\Service\DataFixtures;

use App\Entity\Core\Color;
use App\Entity\Core\Configuration;
use App\Entity\Security\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * ColorFixture
 *
 * Color Fixture management
 *
 * @property array COLORS
 * @property array ACTIVE_COLORS
 * @property array FAVICON
 * @property array CATEGORIES
 *
 * @property EntityManagerInterface $entityManager
 * @property TranslatorInterface $translator
 * @property int $position
 * @property array $yamlConfiguration
 * @property User $user
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ColorFixture
{
    private const COLORS = [
        'primary' => '#8c3744',
        'secondary' => '#333333',
        'success' => '#28a745',
        'danger' => '#dc3545',
        'warning' => '#ffc107',
        'info' => '#17a2b8',
        'light' => '#dee2e6',
        'dark' => '#343a40',
        'link' => '#007bff',
        'white' => '#ffffff'
    ];
    private const ACTIVE_COLORS = [
        'primary',
        'secondary',
        'white',
        'alert-success',
        'alert-danger',
        'alert-warning',
        'alert-info',
        'mask-icon',
        'msapplication-TileColor',
        'theme-color',
        'webmanifest-theme',
        'webmanifest-background',
        'browserconfig',
        'maintenance'
    ];
    private const FAVICON = [
        'mask-icon' => '#8c3744',
        'msapplication-TileColor' => '#8c3744',
        'theme-color' => '#ffffff',
        'webmanifest-theme' => '#ffffff',
        'webmanifest-background' => '#ffffff',
        'browserconfig' => '#8c3744'
    ];
    private const CATEGORIES = [
        'button', 'button-outline', 'color', 'background', 'alert', 'favicon'
    ];

    private $entityManager;
    private $translator;
    private $position = 1;
    private $yamlConfiguration = [];
    private $user;

    /**
     * ColorFixture constructor.
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
     * Add Colors
     *
     * @param Configuration $configuration
     * @param array $yamlConfiguration
     * @param User|null $user
     */
    public function add(Configuration $configuration, array $yamlConfiguration, User $user = NULL)
    {
        $this->yamlConfiguration = $yamlConfiguration;
        $this->user = $user;

        foreach (self::CATEGORIES as $category) {
            $this->generateColors($configuration, $category);
        }
    }

    /**
     * Generate colors
     *
     * @param Configuration $configuration
     * @param string $category
     */
    private function generateColors(Configuration $configuration, string $category)
    {
        $selfFavicons = !empty($this->yamlConfiguration['favicons']) ? $this->yamlConfiguration['favicons'] : self::FAVICON;
        $selfColors = !empty($this->yamlConfiguration['colors']) ? $this->yamlConfiguration['colors'] : self::COLORS;
        $colors = $category === 'favicon' ? $selfFavicons : $selfColors;

        foreach ($colors as $code => $hexadecimal) {
            if ($code !== 'link' && $category !== 'button' || $category === 'button') {
                $this->generateColor($configuration, $code, $hexadecimal, $category);
            }
        }

        $this->generateColor($configuration, 'maintenance', '#410c2c', 'background');
    }

    /**
     * Generate color
     *
     * @param Configuration $configuration
     * @param string $code
     * @param string $hexadecimal
     * @param string $category
     */
    private function generateColor(Configuration $configuration, string $code, string $hexadecimal, string $category)
    {
        $categoryConfiguration = (object)$this->getCategoryConfiguration($category, $code);
        $slug = $categoryConfiguration->prefix . '-' . $code;
        $activeColors = !empty($this->yamlConfiguration['active_colors']) ? $this->yamlConfiguration['active_colors'] : self::ACTIVE_COLORS;
        $isActive = in_array($code, $activeColors) || in_array($slug, $activeColors);

        $color = new Color();
        $color->setAdminName($categoryConfiguration->adminName);
        $color->setSlug(ltrim($slug, '-'));
        $color->setCategory($categoryConfiguration->category);
        $color->setDeletable(false);
        $color->setIsActive($isActive);
        $color->setColor($hexadecimal);
        $color->setPosition($this->position);
        $color->setConfiguration($configuration);

        if ($this->user) {
            $color->setCreatedBy($this->user);
        }

        $this->entityManager->persist($color);
        $this->position++;
    }

    /**
     * Get category configuration
     *
     * @param string $category
     * @param string $code
     * @return array
     */
    private function getCategoryConfiguration(string $category, string $code): array
    {
        if ($code === 'link') {
            return [
                'adminName' => $this->getTranslation($code),
                'prefix' => NULL,
                'category' => 'button'
            ];
        }

        $adminNames['button'] = [
            'adminName' => $this->translator->trans('Bouton', [], 'admin'),
            'prefix' => 'btn',
            'category' => 'button'
        ];

        $adminNames['button-outline'] = [
            'adminName' => $this->translator->trans('Button avec contour', [], 'admin'),
            'prefix' => 'btn-outline',
            'category' => 'button'
        ];

        $adminNames['color'] = [
            'adminName' => $this->translator->trans('Couleur principale', [], 'admin'),
            'category' => 'color'
        ];

        $adminNames['background'] = [
            'adminName' => $this->translator->trans('Couleur de fond', [], 'admin'),
            'prefix' => 'bg',
            'category' => 'background'
        ];

        $adminNames['alert'] = [
            'adminName' => $this->translator->trans("Couleur des alertes", [], 'admin'),
            'prefix' => 'alert',
            'category' => 'alert'
        ];

        $adminNames['favicon'] = [
            'adminName' => $this->translator->trans('Couleur de fond', [], 'admin'),
            'category' => 'favicon'
        ];

        return [
            'adminName' => isset($adminNames[$category]['adminName'])
                ? $adminNames[$category]['adminName'] . ' ' . $this->getTranslation($code)
                : $this->getTranslation($code),
            'prefix' => isset($adminNames[$category]['prefix']) ? $adminNames[$category]['prefix'] : NULL,
            'category' => isset($adminNames[$category]['category']) ? $adminNames[$category]['category'] : NULL
        ];
    }

    /**
     * Get translation
     *
     * @param string $code
     * @return string
     */
    private function getTranslation(string $code): string
    {
        $translations = [
            'primary' => $this->translator->trans('principal', [], 'admin'),
            'white' => $this->translator->trans('blanc', [], 'admin'),
            'secondary' => $this->translator->trans('secondaire', [], 'admin'),
            'success' => $this->translator->trans('de success', [], 'admin'),
            'danger' => $this->translator->trans("d'erreur", [], 'admin'),
            'warning' => $this->translator->trans("d'alerte", [], 'admin'),
            'info' => $this->translator->trans("d'information", [], 'admin'),
            'light' => $this->translator->trans('inactif', [], 'admin'),
            'dark' => $this->translator->trans('noir', [], 'admin'),
            'link' => $this->translator->trans('Lien classique', [], 'admin')
        ];

        return isset($translations[$code]) ? $translations[$code] : $code;
    }
}