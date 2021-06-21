<?php

namespace App\Twig\Content;

use App\Entity\Seo\Url;
use App\Service\Content\SeoService;
use Doctrine\ORM\NonUniqueResultException;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * SeoRuntime
 *
 * @property SeoService $seoService
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SeoRuntime implements RuntimeExtensionInterface
{
    private $seoService;

    /**
     * ApiRuntime constructor.
     *
     * @param SeoService $seoService
     */
    public function __construct(SeoService $seoService)
    {
        $this->seoService = $seoService;
    }

	/**
	 * Get Seo
	 *
	 * @param Url $url
	 * @param $entity
	 * @return array|false
	 */
    public function seo(Url $url, $entity)
    {
        try {
            return $this->seoService->execute($url, $entity);
        } catch (NonUniqueResultException $e) {
        } catch (\Exception $e) {
        }
    }
}