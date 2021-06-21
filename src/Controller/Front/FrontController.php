<?php

namespace App\Controller\Front;

use App\Entity\Core\Website;
use App\Entity\Layout\Block;
use App\Entity\Layout\BlockType;
use App\Entity\Layout\Page;
use App\Entity\Media\ThumbAction;
use App\Entity\Media\ThumbConfiguration;
use App\Entity\Security\User;
use App\Entity\Seo\Url;
use App\Repository\Core\WebsiteRepository;
use Exception;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

/**
 * FrontController
 *
 * Front base controller
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FrontController extends CacheController
{
	/**
	 * Get Block render view
	 *
	 * @Route("/front/render/block/{website}/{block}/{url}/{isIndex}/{transitions}/{mediaWidth}/{mediaHeight}", name="front_render_toolbox", methods={"GET"}, schemes={"%protocol%"})
	 *
	 * @param Request $request
	 * @param Website $website
	 * @param Block $block
	 * @param Url $url
	 * @param bool $isIndex
	 * @param int|null $mediaHeight
	 * @param int|null $mediaWidth
	 * @param string|null $transitions
	 * @return Response
	 * @throws Exception
	 */
	public function block(Request $request, Website $website, Block $block, Url $url, bool $isIndex, string $transitions = NULL, int $mediaHeight = NULL, int $mediaWidth = NULL): Response
	{
		$col = $block->getCol();
		$zone = $col->getZone();
		$blockTemplate = $block->getBlockType()->getSlug();
		$configuration = $website->getConfiguration();
		$websiteTemplate = $configuration->getTemplate();
		$template = 'front/' . $websiteTemplate . '/blocks/' . $blockTemplate . '/' . $block->getTemplate() . '.html.twig';

		/** Get Block Transition[] */
		$transitionsRequest = $transitions ? json_decode($transitions) : (object)[];
		$blockTransitions = [];
		foreach ($transitionsRequest as $slug => $config) {
			if ($config->section === 'block-' . $blockTemplate) {
				$blockTransitions[$slug] = $config;
			}
		}

		$alignment = $block->getAlignment() ?: ($col->getAlignment() ?: ($zone->getAlignment() ?: 'left'));

		return $this->cache($template, $block, [
			'websiteTemplate' => $websiteTemplate,
			'block' => $block,
			'isIndex' => $isIndex,
			'website' => $website,
			'alignment' => 'text-' . $alignment,
			'thumbConfiguration' => $this->thumbConfiguration($website, Block::class, NULL, $block->getBlockType()->getSlug()),
			'mediaWidth' => $mediaWidth,
			'mediaHeight' => $mediaHeight,
			'blockTransitions' => $blockTransitions,
			'transitions' => $transitionsRequest,
			'url' => $url,
			'seo' => $request->getSession()->get('front_seo')
		], $configuration->getFullCache());
	}

	/**
	 * Webmaster toolbox
	 *
	 * @Route("/front/render/html/webmaster/toolbox/ajax/{website}/{interfaceName}/{entityId}/{url}", methods={"GET"}, name="front_webmaster_toolbox", options={"expose"=true}, schemes={"%protocol%"})
	 *
	 * @param Website $website
	 * @param string|null $interfaceName
	 * @param int|null $entityId
	 * @param Url|null $url
	 * @return Response
	 */
	public function toolBox(Website $website, string $interfaceName = NULL, int $entityId = NULL, Url $url = NULL)
	{
		if (in_array(@$_SERVER['REMOTE_ADDR'], $website->getConfiguration()->getAllIPS(), true)
			|| $this->getUser() instanceof User) {
			return new JsonResponse(['html' => $this->renderView('core/webmaster-box.html.twig', [
				'renderToolbox' => true,
				'interfaceName' => $interfaceName,
				'entityId' => $entityId,
				'website' => $website,
				'url' => $url,
				'configuration' => $website->getConfiguration()
			])]);
		}

		return new Response();
	}

	/**
	 * Get Page with Listing
	 *
	 * @param Website $website
	 * @param string $locale
	 * @param string $classname
	 * @param bool $preview
	 * @return Page[]
	 */
	protected function getListingPages(Website $website, string $locale, string $classname, $preview = false): array
	{
		$repository = $this->entityManager->getRepository(Page::class);

		return $repository->optimizedQueryBuilder($website, $locale, $preview)
			->andWhere('a.entity = :entity')
			->setParameter('entity', $classname)
			->getQuery()
			->getResult();
	}

	/**
	 * To detect activity
	 *
	 * @Route("/front/activity", methods={"GET"}, name="front_activity", options={"expose"=true}, schemes={"%protocol%"})
	 * @return JsonResponse
	 */
	public function activity(): JsonResponse
	{
		return new JsonResponse(['success' => true], 200);
	}

	/**
	 * To set website alert user session
	 *
	 * @Route("/front/website-alert", methods={"GET"}, name="website_alert", options={"expose"=true}, schemes={"%protocol%"})
	 *
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function websiteAlert(Request $request): JsonResponse
	{
		$request->getSession()->set('front_website_alert_show', true);

		return new JsonResponse(['success' => true], 200);
	}

	/**
	 * Track emails sends
	 *
	 * @Route("/front/emails/resources/{code}", methods={"GET"}, name="front_track_email", schemes={"%protocol%"})
	 *
	 * @param string $code
	 * @return Response
	 */
	public function trackEmails(string $code)
	{
		$logger = new Logger('form.global.manager');
		$logger->pushHandler(new RotatingFileHandler($this->kernel->getLogDir() . '/emails-tracks.log', 10, Logger::INFO));
		$logger->info('Message ouvert.');

		$file = $this->kernel->getProjectDir() . '/assets/medias/images/vendor/lazy-point.svg';
		$file = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $file);
		return new BinaryFileResponse($file);
	}

	/**
	 * Show Website
	 *
	 * @Route("/front/website/selector/{website}", methods={"GET"})
	 *
	 * @param WebsiteRepository $websiteRepository
	 * @param Website $website
	 * @return Response
	 * @throws Exception
	 */
	public function websitesSelector(WebsiteRepository $websiteRepository, Website $website): Response
	{
		return $this->cache('front/' . $website->getConfiguration()->getTemplate() . '/include/websites-selector.html.twig', NULL, [
			'currentWebsite' => $website,
			'websites' => $websiteRepository->findAll()
		]);
	}

    /**
     * Get Thumb
     *
     * @param Website $website
     * @param string $classname
     * @param string|null $action
     * @param int|string|object|null $filter
     * @param string|null $type
     * @return ThumbConfiguration|null
     */
    protected function thumbConfiguration(Website $website, string $classname, string $action = NULL, $filter = NULL, string $type = NULL): ?ThumbConfiguration
    {
        $session = new Session();
        $thumbs = $session->get('thumbs_actions_' . $website->getId());

        if (!$thumbs) {
            $thumbsActions = $this->entityManager->getRepository(ThumbAction::class)->findByWebsite($website);
            $thumbsActionsSession = [];
            foreach ($thumbsActions as $thumbAction) {
                /** @var ThumbAction $thumbAction */
                $thumbsActionsSession[$thumbAction->getNamespace()][] = $thumbAction;
            }
            $session->set('thumbs_actions_' . $website->getId(), $thumbsActionsSession);
        }

        $thumbsActions = !empty($thumbs[$classname]) ? $thumbs[$classname] : [];

        foreach ($thumbsActions as $thumbAction) {
            if (!$action && !$thumbAction->getAction() && !$filter && !$thumbAction->getActionFilter() && !$type && !$thumbAction->getBlockType()) {
                return $thumbAction->getConfiguration();
            } else if ((is_object($filter) && $thumbAction->getAction() === $action) && (method_exists($filter, 'getSlug') && $filter->getSlug() == $thumbAction->getActionFilter() || method_exists($filter, 'getId') && $filter->getId() == $thumbAction->getActionFilter())) {
                return $thumbAction->getConfiguration();
            } else if ($classname === Block::class && $thumbAction->getBlockType() instanceof BlockType && $filter === $thumbAction->getBlockType()->getSlug()) {
                return $thumbAction->getConfiguration();
            } else if ($thumbAction->getAction() === $action && $thumbAction->getActionFilter() === $filter) {
                return $thumbAction->getConfiguration();
            }
        }

        return NULL;
    }

	/**
	 * Get Thumb by filter
	 *
	 * @param Website $website
	 * @param string $classname
	 * @param null $filter
	 * @return ThumbConfiguration|null
	 */
	protected function thumbConfigurationByFilter(Website $website, string $classname, $filter = NULL): ?ThumbConfiguration
	{
		/** @var ThumbAction $thumbAction */
		$thumbAction = $this->entityManager->getRepository(ThumbAction::class)->findByNamespaceAndFilter($website, $classname, $filter);
		return $thumbAction ? $thumbAction->getConfiguration() : NULL;
	}
}