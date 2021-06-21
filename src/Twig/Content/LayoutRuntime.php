<?php

namespace App\Twig\Content;

use App\Controller\Front\FrontController;
use App\Entity\Core\Transition;
use App\Entity\Core\Website;
use App\Entity\Layout\Action;
use App\Entity\Layout\Block;
use App\Entity\Layout\Col;
use App\Entity\Layout\Layout;
use App\Entity\Layout\Zone;
use App\Entity\Media\Thumb;
use App\Entity\Media\ThumbAction;
use App\Entity\Seo\Url;
use App\Entity\Translation\i18n;
use App\Service\Core\CacheService;
use App\Twig\Translation\i18nRuntime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * LayoutRuntime
 *
 * @property FileRuntime $fileExtension
 * @property EntityManagerInterface $entityManager
 * @property i18nRuntime $i18nRuntime
 * @property RequestStack $requestStack
 * @property KernelInterface $kernel
 * @property FileRuntime $fileRuntime
 * @property Request $request
 * @property CacheService $cacheService
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class LayoutRuntime implements RuntimeExtensionInterface
{
	private $fileExtension;
	private $entityManager;
	private $i18nRuntime;
	private $requestStack;
	private $kernel;
	private $fileRuntime;
	private $request;
	private $cacheService;

	/**
	 * LayoutRuntime constructor.
	 *
	 * @param FileRuntime $fileExtension
	 * @param EntityManagerInterface $entityManager
	 * @param i18nRuntime $i18nRuntime
	 * @param RequestStack $requestStack
	 * @param KernelInterface $kernel
	 * @param FileRuntime $fileRuntime
	 * @param CacheService $cacheService
	 */
	public function __construct(
		FileRuntime $fileExtension,
		EntityManagerInterface $entityManager,
		i18nRuntime $i18nRuntime,
		RequestStack $requestStack,
		KernelInterface $kernel,
		FileRuntime $fileRuntime,
		CacheService $cacheService)
	{
		$this->fileExtension = $fileExtension;
		$this->entityManager = $entityManager;
		$this->i18nRuntime = $i18nRuntime;
		$this->requestStack = $requestStack;
		$this->kernel = $kernel;
		$this->fileRuntime = $fileRuntime;
		$this->request = $requestStack->getMasterRequest();
		$this->cacheService = $cacheService;
	}

	/**
	 * Get Layout renders
	 *
	 * @param Layout $layout
	 * @param array $website
	 * @param bool $isIndex
	 * @param array $url
	 * @param string|null $transitions
	 * @return array
	 * @throws Exception
	 */
	public function layoutRenders(
		Layout $layout,
		array $website,
		bool $isIndex,
		array $url = [],
		string $transitions = NULL): array
	{
		$locale = $this->request->getLocale();
		$configuration = $website['configuration'];
		$websiteTemplate = $configuration['template'];
		$renders = [];

		if ($layout instanceof Layout) {
			$zones = $this->entityManager->getRepository(Zone::class)->findByLayoutArray($layout);
			foreach ($zones as $zone) {
				$renders['zones'][$zone['id']] = $zone;
				$mediasSizes = $zone['standardizeMedia'] ? $this->mediasHeight($website, $zone, $locale) : NULL;
				$mediaWidth = $mediasSizes ? $mediasSizes['width'] : NULL;
				$mediaHeight = $mediasSizes ? $mediasSizes['height'] : NULL;
				foreach ($zone['cols'] as $col) {
					foreach ($col['blocks'] as $block) {
						$action = $block['action'];
						$blockId = $block['id'];
						$blockTypeSlug = $block['blockType']['slug'];
						$blockTemplate = $block['template'];
						$block = $this->entityManager->getRepository(Block::class)->find($block['id']);
						/** Get Action */
						if ($blockTypeSlug === 'core-action') {
							$actionI18ns = $this->i18nRuntime->i18nAction($block);
							$actionFilter = $actionI18ns ? $actionI18ns->getActionFilter() : NULL;
							if (!empty($action['controller'])) {
								$renders['actions'][$blockId] = $this->forward($action['controller'] . '::' . $action['action'], [
									'website' => $website['id'],
									'block' => $block,
									'url' => $url['id'],
									'filter' => $actionFilter,
									'isIndex' => $isIndex
								])->getContent();
							}
						} /** Get Blocks */ else {
							$template = 'front/' . $websiteTemplate . '/blocks/' . $blockTypeSlug . '/' . $blockTemplate . '.html.twig';
							if ($this->fileRuntime->fileExist($template)) {
								$renders['blocks'][$blockId] = $this->forward(FrontController::class . '::block', [
									'website' => $website['id'],
									'block' => $block,
									'url' => $url['id'],
									'isIndex' => $isIndex,
									'transitions' => $transitions,
									'mediaHeight' => $mediaHeight,
									'mediaWidth' => $mediaWidth,
								])->getContent();
							}
						}
					}
				}
			}
		}

		return $renders;
	}

	/**
	 * Get forward Controller
	 *
	 * @param string $controller
	 * @param array $path
	 * @return Response
	 * @throws Exception
	 */
	private function forward(string $controller, array $path = [])
	{
		$path['_controller'] = $controller;
		$subRequest = $this->requestStack->getCurrentRequest()->duplicate([], null, $path);
		return $this->kernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
	}

	/**
	 * Get Zone classes
	 *
	 * @param array $zone
	 * @return string
	 */
	public function zoneClasses(array $zone): string
	{
		$backgroundColor = $zone['backgroundColor'];
		$transition = $zone['transition'];
		$customClass = $zone['customClass'];
		$alignment = $zone['alignment'];

		$class = $backgroundColor != ('' and 'transparent') && $zone['backgroundFullSize'] ? ' ' . $backgroundColor : ' bg-none';
		$class .= $zone['hide'] || !$this->getZoneDisplay($zone) ? ' d-none' : '';
		$class .= $customClass ? ' ' . $customClass : '';
		$class .= $zone['backgroundFixed'] ? ' bg-fixed' : '';
		$class .= $zone['backgroundParallax'] ? ' parallax-window' : '';
		$class .= $zone['fullSize'] ? ' full-size' : ' container-size';
		$class .= $alignment ? ' text-' . $alignment : '';

		if (!empty($transition)) {
			$class .= $transition['aosEffect'] ? ' aos' : '';
			$class .= $transition['laxPreset'] ? ' lax' : '';
		}

		return trim($class);
	}

	/**
	 * Get Col classes by size and grid
	 *
	 * @param array $col
	 * @param array $zone
	 * @param string $grid
	 * @return string
	 */
	public function colClasses(array $col, array $zone, string $grid): string
	{
		/** Configure grid */
		$grids = [];
		$grids[6]['6-6'] = 'col-12 col-lg-6';
		$grids[3]['3-3-3-3'] = 'col-12 col-lg-6 col-xl-3';
		$grids[2]['2-2-2-2-2-2'] = 'col-6 col-xl-2';
		$grids[8]['2-8-2'] = 'col-12 col-xl-8';
		$grids[10]['1-10-1'] = 'col-12 col-xl-10';
		$grids[5]['1-5-1-4-1'] = 'col-12 col-lg-6 col-xl-5';
		$grids[4]['1-5-1-4-1'] = 'col-12 col-lg-6 col-xl-4';
		$grids[10]['1-10-1'] = 'col-12 col-xl-10';

		$size = $col['size'];
		$mobileSize = $col['mobileSize'] ?: 'col-12';
		$tabletSize = $col['tabletSize'] ?: 'col-lg-' . $size;
		$grid = !empty($grids[$size][$grid]) ? $grids[$size][$grid] : $mobileSize . ' ' . $tabletSize;
		$gridMatches = explode(' ', $grid);
		foreach ($gridMatches as $match) {
			if (preg_match('/col-lg-/', $match) && $col['tabletSize']) {
				$grid = str_replace($match, 'col-lg-' . $col['tabletSize'], $grid);
			} elseif (preg_match('/col-/', $match) && !preg_match('/col-xl-/', $match) && $col['mobileSize']) {
				$grid = str_replace($match, 'col-' . $col['mobileSize'], $grid);
			}
		}

		$backgroundColor = $col['backgroundColor'];
		$alignment = $col['alignment'];
		$transition = $col['transition'];

		$class = $zone['standardizeElements'] ? 'col-center col-12 col-md-6 col-lg' : $grid;
		$class .= $zone['centerCol'] ? ' mx-auto' : '';
		$class .= $col['endAlign'] ? ' d-flex align-items-end' : '';
		$class .= $col['backgroundFullSize'] && $backgroundColor !== (NULL && 'transparent') ? ' ' . $backgroundColor : '';
		$class .= $col['reverse'] ? ' mobile-first' : '';
		$class .= $alignment ? ' text-' . $alignment : '';
		$class .= $col['hide'] ? ' d-none' : '';
		$class .= $this->elementOrders($col);

		if (!empty($transition)) {
			$class .= $transition['aosEffect'] ? ' aos' : '';
			$class .= $transition['laxPreset'] ? ' lax' : '';
		}

		if ($zone['centerColsGroup'] && $col['position'] === 1) {
			$sizeCount = 0;
			foreach ($zone['cols'] as $zoneCol) {
				$sizeCount = $sizeCount + $zoneCol['size'];
			}
			if ($sizeCount > 0 && $sizeCount < 12) {
				$offset = (12 - $sizeCount) / 2;
				$class .= ' offset-lg-' . $offset;
			}
			$class .= $col['verticalAlign'] ? ' vertical-align-lg row' : '';
		} else {
			$class .= $col['verticalAlign'] ? ' row m-0' : '';
		}

		return trim($class);
	}

	/**
	 * Get Block classes
	 *
	 * @param array $block
	 * @param array $col
	 * @param string|null $colClasses
	 * @return string
	 */
	public function blockClasses(array $block, array $col, string $colClasses = NULL): string
	{
		$transition = $block['transition'];
		$alignment = $block['alignment'];
		$backgroundColor = $block['backgroundColor'];

		$class = $block['verticalAlign'] ? ' my-auto' : '';
		$class .= $block['endAlign'] ? ' d-flex align-items-end' : '';
		$class .= $block['reverse'] ? ' mobile-first' : '';
		$class .= $block['hideMobile'] ? ' d-none d-md-block' : '';
		$class .= $block['backgroundFullSize'] && $backgroundColor !== (NULL && 'transparent') ? ' ' . $backgroundColor : '';
		$class .= $alignment ? ' text-' . $alignment : '';
		$class .= $block['hide'] ? ' d-none' : '';
		$class .= $col['standardizeElements'] ? ' col-sm-12 col-md-6 col-lg' : $this->elementSizes($block);
		$class .= $this->elementOrders($block);

		if (!empty($transition)) {
			$class .= $transition['aosEffect'] ? ' aos' : '';
			$class .= $transition['laxPreset'] ? ' lax' : '';
		}

		$class .= preg_match('/col-full-size/', $colClasses) ? ' h-100' : '';

		return rtrim($class);
	}

	/**
	 * Get element orders
	 *
	 * @param array $element
	 * @return string
	 */
	public function elementOrders(array $element): string
	{
		/** Desktop */
		$desktopPosition = $element['position'];
		$desktopOrder = 'order-lg-' . $desktopPosition;

		/** Tablet */
		$tabletPosition = $element['tabletPosition'] ?: $desktopPosition;
		$tabletOrder = 'order-md-' . $tabletPosition;

		/** Mobile */
		$mobilePosition = $element['mobilePosition'];
		$mobileOrder = $mobilePosition ? 'order-' . $mobilePosition : 'order-' . $desktopPosition;

		return ' ' . $mobileOrder . ' ' . $tabletOrder . ' ' . $desktopOrder;
	}

	/**
	 * Get element sizes
	 *
	 * @param array $element
	 * @return string
	 */
	public function elementSizes(array $element): string
	{
		/** Desktop */
		$desktopDefaultSize = $element['size'];
		$desktopOrder = 'col-lg-' . $desktopDefaultSize;

		/** Tablet */
		$tabletSize = $element['tabletSize'] ?: 12;
		$tabletSize = 'col-md-' . $tabletSize;

		/** Mobile */
		$mobileSize = $element['mobileSize'];
		$mobileSize = $mobileSize ? 'col-' . $mobileSize : 'col-12';

		return ' ' . $mobileSize . ' ' . $tabletSize . ' ' . $desktopOrder;
	}

	/**
	 * Get effects attributes
	 *
	 * @param mixed|null $entity
	 * @return string
	 */
	public function effectsAttrs($entity = NULL): string
	{
		$attributes = '';
		$transition = is_object($entity) && method_exists($entity, 'getTransition') ? $entity->getTransition() : NULL;

		if ($transition instanceof Transition) {

			$laxAttributes = $this->laxEffects($transition);
			if ($laxAttributes) {
				$attributes .= $laxAttributes;
			}

			$aosAttributes = $this->aosEffect($transition, $entity);
			if (!$laxAttributes && $aosAttributes) {
				$attributes .= ' ' . $aosAttributes;
			}
		}

		return rtrim($attributes);
	}

	/**
	 * Get standardize Media[] height
	 *
	 * @param array $website
	 * @param array $zone
	 * @param string $locale
	 * @return array
	 */
	public function mediasHeight(array $website, array $zone, string $locale): array
	{
		$isSet = false;
		$width = 0;
		$height = $initHeight = 100000000000;

		if ($zone['standardizeMedia']) {

			$thumbRepository = $this->entityManager->getRepository(ThumbAction::class);
			$thumbAction = $thumbRepository->findForEntity($website, Block::class, NULL, NULL, 'media');
			$thumbConfiguration = $thumbAction ? $thumbAction['configuration'] : NULL;
			$standardizeBlocksTypes = ['media', 'card'];

			foreach ($zone['cols'] as $col) {
				foreach ($col['blocks'] as $block) {
					$blockTypeSlug = $block['blockType']['slug'];
					if (in_array($blockTypeSlug, $standardizeBlocksTypes)) {
						foreach ($block['mediaRelations'] as $mediaRelation) {
							if ($mediaRelation['locale'] === $locale) {
								$isSet = true;
								$media = $mediaRelation['media'];
								$height = $mediaRelation['maxHeight'] > 0 ? $mediaRelation['maxHeight'] : $height;
								$width = $mediaRelation['maxWidth'] > 0 ? $mediaRelation['maxWidth'] : $width;
								$fileInfo = $this->fileExtension->fileInfo($website, $media['filename']);
								if (is_object($fileInfo) && property_exists($fileInfo, 'height') && $fileInfo->height < $height && property_exists($fileInfo, 'width')) {
									$height = $fileInfo->height;
									$width = $fileInfo->width;
								}
								if ($thumbConfiguration) {
									$thumb = NULL;
									foreach ($media['thumbs'] as $mediaThumb) {
										if ($mediaThumb['configuration'] === $thumbConfiguration) {
											$thumb = $mediaThumb;
											break;
										}
									}
									$height = !empty($thumb['height']) && $thumb['height'] < $height ? $thumb['height'] : $height;
									$width = !empty($thumb['height']) && $thumb['height'] < $height ? $thumb['width'] : $width;
								}
							}
						}
					}
				}
			}
		}

		return [
			'width' => $width,
			'height' => $isSet && $height !== $initHeight ? $height : NULL
		];
	}

	/**
	 * Get Layout main title
	 *
	 * @param mixed $layout
	 * @param string|null $locale
	 * @param bool $all
	 * @param bool $hasArray
	 * @return mixed
	 */
	public function mainLayoutTitle($layout, string $locale = NULL, bool $all = false, bool $hasArray = false)
	{
		$locale = $locale ?: $this->request->getLocale();
		$repository = $this->entityManager->getRepository(Block::class);
		$title = $repository->findTitleByForceAndLocaleLayout($layout, $locale, 1, $all, $hasArray);

		if (!$title) {
			$title = $repository->findTitleByForceAndLocaleLayout($layout, $locale, 2, $all, $hasArray);
		}

		return $title;
	}

	/**
	 * Get block type by Layout and slug
	 *
	 * @param mixed $layout
	 * @param string|null $slug
	 * @param string|null $locale
	 * @return mixed|null
	 */
	public function layoutBlockType($layout, string $slug = NULL, string $locale = NULL)
	{
		if (!$layout || !$slug) {
			return NULL;
		}

		$locale = $locale ?: $this->request->getLocale();
		$repository = $this->entityManager->getRepository(Block::class);
		return $repository->findByBlockTypeAndLocaleLayout($layout, $slug, $locale);
	}

	/**
	 * Get margins element
	 *
	 * @param array $entity
	 * @return string
	 */
	public function margins(array $entity = []): string
	{
		$classes = '';
		$fullSize = isset($entity['blocks']) && $entity['fullSize'];
		$sides = ['top', 'right', 'bottom', 'left'];

		foreach ($sides as $side) {
			$getter = 'margin' . ucfirst($side);
			if (preg_match('/neg/', $entity[$getter])) {
				$classes .= ' negative-margin';
			}
		}

		if ($fullSize) {
			$classes .= ' ms-0 me-0 col-full-size';
		} else {

			$marginRight = $entity['marginRight'];
			if ($marginRight) {
				$classes .= ' ' . $marginRight;
			}

			$marginLeft = $entity['marginLeft'];
			if ($marginLeft) {
				$classes .= ' ' . $marginLeft;
			}
		}

		$marginTop = $entity['marginTop'];
		if ($marginTop) {
			$classes .= ' ' . $marginTop;
		}

		$marginBottom = $entity['marginBottom'];
		if ($marginBottom) {
			$classes .= ' ' . $marginBottom;
		}

		return !$classes ? '' : $classes;
	}

	/**
	 * Get paddings element
	 *
	 * @param array $entity
	 * @return string
	 */
	public function paddings(array $entity = []): string
	{
		$classes = '';
		$fullSize = isset($entity['blocks']) && $entity['fullSize'];

		if ($fullSize) {
			$classes .= ' ps-0 pe-0 col-full-size';
		} else {

			$paddingRight = $entity['paddingRight'];
			$classes .= $paddingRight ? ' ' . $paddingRight : (!isset($entity['blockType']) || $entity['size'] < 12 ? ' pe-sm' : ' ');

			$paddingLeft = $entity['paddingLeft'];
			$classes .= $paddingLeft ? ' ' . $paddingLeft : (!isset($entity['blockType']) || $entity['size'] < 12 ? ' ps-sm' : ' ');
		}

		$paddingTop = $entity['paddingTop'];
		if ($paddingTop) {
			$classes .= ' ' . $paddingTop;
		}

		$paddingBottom = $entity['paddingBottom'];
		if ($paddingBottom) {
			$classes .= ' ' . $paddingBottom;
		}

		return !$classes ? '' : $classes;
	}

	/**
	 * Check if Zone display
	 *
	 * @param array $zone
	 * @return bool
	 */
	private function getZoneDisplay(array $zone): bool
	{
		$elCount = 0;
		foreach ($zone['cols'] as $col) {
			if (count($col['blocks']) > 0) {
				$elCount++;
				break;
			}
		}
		return $elCount > 0;
	}

    /**
     * Get all transitions
     *
     * @param Website $website
     * @return array
     */
    public function transitions(Website $website): array
    {
        $transitions = [];
        $configuration = $website->getConfiguration();
        $fieldNames = $this->entityManager->getClassMetadata(Transition::class)->getFieldNames();

        foreach ($configuration->getTransitions() as $transition) {
            if ($transition->getIsActive()) {
                foreach ($fieldNames as $fieldName) {
                    $getter = 'get' . ucfirst($fieldName);
                    $value = $transition->$getter();
                    if (!is_object($value)) {
                        $transitions[$transition->getSlug()][$fieldName] = $value;
                    }
                }
            }
        }

        return $transitions;
    }

	/**
	 * Get AOS effect attributes
	 *
	 * @param Transition|null $transition
	 * @param mixed|null $entity
	 * @return string
	 */
	private function aosEffect(Transition $transition = NULL, $entity = NULL): string
	{
		$attributes = '';

		if ($transition && is_object($entity)) {

			$attributes = 'data-aos="' . $transition->getAosEffect() . '"';

			$duration = method_exists($entity, 'getDuration') && $entity->getDuration() > 0 ? $entity->getDuration() : ($transition->getDuration() > 0 ? $transition->getDuration() : NULL);
			if ($duration) {
				$attributes .= ' data-aos-duration="' . $duration . '"';
			}

			$delay = method_exists($entity, 'getDelay') && $entity->getDelay() > 0 ? $entity->getDelay() : ($transition->getDelay() > 0 ? $transition->getDelay() : NULL);
			if ($delay) {
				$attributes .= ' data-aos-delay="' . $delay . '"';
			}
		}

		return rtrim($attributes);
	}

	/**
	 * Get Lax effects attributes
	 *
	 * @param Transition|null $transition
	 * @return string
	 */
	private function laxEffects(Transition $transition = NULL): ?string
	{
		$attributes = '';

		$effects = $transition ? $transition->getLaxPreset() : [];
		foreach ($effects as $effect) {
			$attributes .= $effect . ' ';
		}

		return $attributes ? 'data-lax-anchor="self" data-lax-preset="' . rtrim($attributes) . '"' : NULL;
	}

	/**
	 * Get transition attributes
	 *
	 * @param string|null $slug
	 * @param array|null $configurations
	 * @return array
	 */
	public function transitionAttributes(string $slug = NULL, ?array $configurations = []): array
	{
		$attributes['class'] = '';
		$attributes['attr'] = '';

		if ($slug && !empty($configurations[$slug])) {

			$transition = $configurations[$slug];

			/** AOS */
			if ($transition->aosEffect) {
				$attributes['attr'] .= " data-aos='" . $transition->aosEffect . "' data-aos-once='true'";
				$attributes['attr'] .= " data-aos-once='true'";
			}
			if ($transition->aosEffect && $transition->delay) {
				$attributes['attr'] .= " data-aos-delay='" . $transition->delay . "'";
			}
			if ($transition->aosEffect && $transition->offset) {
				$attributes['attr'] .= " data-aos-offset='" . $transition->offset . "'";
			}

			/** Lax */
			if ($transition->laxPreset || $transition->parameters) {
				$attributes['class'] .= " lax";
				$attributes['attr'] .= " data-lax-anchor='self'";
			}
			if ($transition->laxPreset) {
				$preset = '';
				foreach ($transition->laxPreset as $effect) {
					$preset .= ' ' . $effect;
				}
				$attributes['attr'] .= " data-lax-preset='" . trim($preset) . "'";
			}
			if ($transition->parameters) {
				$attributes['attr'] .= $transition->parameters;
			}
		}

		return $attributes;
	}

	/**
	 * Get Block cache render
	 *
	 * @param Website $website
	 * @param mixed|null $entity
	 * @param array $options
	 * @return string|null
	 */
	public function cache(Website $website, $entity = NULL, array $options = []): ?string
	{
		$html = NULL;
		$configuration = $website->getConfiguration();
        $hasFullCache = $configuration->getFullCache();

		if (!empty($options['classname']) && !empty($options['filter'])) {
			$repository = $this->entityManager->getRepository($options['classname']);
			if (is_object($repository) && method_exists($repository, 'findOneByFilter')) {
				$entity = $repository->findOneByFilter($website, $this->request->getLocale(), $options['filter']);
			}
		}

		if ($hasFullCache && is_object($entity)) {
			$response = $this->cacheService->cacheFile($configuration, NULL, $entity);
			$html = $response instanceof Response ? $response->getContent() : $html;
		}

		return $html;
	}
}