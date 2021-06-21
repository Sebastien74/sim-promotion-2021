<?php

namespace App\Twig\Content;

use App\Entity\Core\Color;
use App\Entity\Core\Website;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Calculation\Web;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * ColorRuntime
 *
 * @property Request $request
 * @property EntityManagerInterface $entityManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ColorRuntime implements RuntimeExtensionInterface
{
    private $request;
    private $entityManager;

    /**
     * ComponentRuntime constructor.
     *
     * @param RequestStack $requestStack
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(RequestStack $requestStack, EntityManagerInterface $entityManager)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->entityManager = $entityManager;
    }

	/**
	 * Get colors
	 *
	 * @param mixed $website
	 * @return array
	 */
    public function colors($website): array
	{
        $configurationId = $website instanceof Website ? $website->getConfiguration()->getId() : $website['configuration']['id'];
		$colorsBb = $this->entityManager->getRepository(Color::class)->findByConfiguration($configurationId);

        $colors = [];

		foreach ($colorsBb as $color) {
			if ($color['isActive']) {
				$colors[$color['category']][] = $color['slug'];
			}
		}
		if (empty($colors['background']) || !empty($colors['background']) && !in_array('bg-white', $colors['background'])) {
			$colors['background'][] = 'bg-white';
		}

        return $colors;
    }

    /**
     * Get color
     *
     * @param string $category
     * @param mixed $website
     * @param string|NULL $slug
     * @return object
     */
    public function color(string $category, $website = NULL, string $slug = NULL)
    {
        $website = !$website || is_object($website) ? $this->entityManager->getRepository(Website::class)->findOneByHost($this->request->getHost(), false, true) : $website;
        $configurationId = $website instanceof Website ? $website->getConfiguration()->getId() : $website['configuration']['id'];
		$colors = $this->entityManager->getRepository(Color::class)->findByConfiguration($configurationId);

		foreach ($colors as $color) {
			if ($color['category'] === $category && $color['slug'] === $slug) {
				return $color;
			}
		}

        return (object)['color' => $slug];
    }
}