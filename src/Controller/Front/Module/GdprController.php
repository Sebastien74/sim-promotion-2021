<?php

namespace App\Controller\Front\Module;

use App\Controller\Front\FrontController;
use App\Entity\Api\Api;
use App\Entity\Gdpr\Group;
use App\Repository\Core\WebsiteRepository;
use App\Repository\Gdpr\CategoryRepository;
use App\Repository\Gdpr\GroupRepository;
use App\Repository\Layout\PageRepository;
use App\Service\Core\GdprService;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * GdprController
 *
 * Front Gdpr renders & management
 *
 * @Route("/gdpr", schemes={"%protocol%"})
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class GdprController extends FrontController
{
	/**
	 * Modal View
	 *
	 * @Route("/html/render/modal.{_format}",
	 *     methods={"GET"},
	 *     name="front_gdpr_modal",
	 *     requirements={"_format" = "json"},
	 *     options={"expose"=true})
	 *
	 * @param Request $request
	 * @param WebsiteRepository $websiteRepository
	 * @param CategoryRepository $categoryRepository
	 * @param PageRepository $pageRepository
	 * @return JsonResponse
	 * @throws NonUniqueResultException
	 */
	public function modal(Request $request, WebsiteRepository $websiteRepository, CategoryRepository $categoryRepository, PageRepository $pageRepository): JsonResponse
	{
		$website = $websiteRepository->findCurrent(true);
		$categories = $categoryRepository->findActiveByConfigurationAndLocale($website['configuration'], $request->getLocale());
		$cookiesPage = $pageRepository->findCookiesPage($website, $request->getLocale());

		return new JsonResponse(['html' => $this->renderView('gdpr/modal.html.twig', [
			'website' => $website,
			'categories' => $categories,
			'cookiesPage' => $cookiesPage
		])]);
	}

	/**
	 * Legacy View
	 *
	 * @Route("/html/render/legacy", methods={"GET"}, name="front_gdpr_legacy", options={"expose"=true})
	 *
	 * @param Request $request
	 * @param WebsiteRepository $websiteRepository
	 * @param CategoryRepository $categoryRepository
	 * @return Response
	 * @throws NonUniqueResultException
	 */
	public function legacy(Request $request, WebsiteRepository $websiteRepository, CategoryRepository $categoryRepository): Response
	{
		$website = $websiteRepository->findCurrent(true);
		$categories = $categoryRepository->findActiveByConfigurationAndLocale($website['configuration'], $request->getLocale());

		return $this->render('gdpr/legacy.html.twig', [
			'website' => $website,
			'categories' => $categories
		]);
	}

	/**
	 * Get Cookies DB
	 *
	 * @Route("/html/cookies/db/{slug}.{_format}",
	 *     methods={"GET"},
	 *     name="front_gdpr_cookies_db",
	 *     requirements={"_format" = "json"},
	 *     options={"expose"=true})
	 *
	 * @param WebsiteRepository $websiteRepository
	 * @param GroupRepository $groupRepository
	 * @param string $slug
	 * @return JsonResponse
	 * @throws NonUniqueResultException
	 */
	public function cookiesDB(WebsiteRepository $websiteRepository, GroupRepository $groupRepository, string $slug): JsonResponse
	{
		$website = $websiteRepository->findCurrent();
		$group = $groupRepository->findByConfiguration($website->getConfiguration(), $slug);
		$cookies = [];

		if ($group) {
			foreach ($group->getGdprcookies() as $cookie) {
				$cookies[] = $cookie->getCode();
			}
		}

		return new JsonResponse([
			'slug' => $group->getSlug(),
			'cookies' => $cookies
		]);
	}

	/**
	 * Get Header scripts and html tags
	 *
	 * @Route("/html/render/scripts.{_format}",
	 *     methods={"GET"},
	 *     name="front_gdpr_scripts",
	 *     requirements={"_format" = "json"},
	 *     options={"expose"=true})
	 *
	 * @param Request $request
	 * @param WebsiteRepository $websiteRepository
	 * @param CategoryRepository $categoryRepository
	 * @return JsonResponse
	 * @throws NonUniqueResultException
	 */
	public function scripts(Request $request, WebsiteRepository $websiteRepository, CategoryRepository $categoryRepository): JsonResponse
	{
		$reload = false;
		$website = $websiteRepository->findCurrent(true);
		$api = $website['api'];
		$cookiesRequest = $this->getCookies($request->cookies->get('felixCookies'));
		$cookies = !empty($cookiesRequest) ? $cookiesRequest : [];
		$cookiesCategories = $categoryRepository->findActiveByConfigurationAndLocale($website['configuration'], $request->getLocale());;

		foreach ($cookies as $service => $status) {
			if (!$status) {
				$reload = true;
			}
		}

		return new JsonResponse([
			'headerScripts' => $this->getScripts($website, $api, 'scripts', $cookies, $cookiesCategories),
			'bodyPrependScripts' => $this->getScripts($website, $api, 'body-prepend', $cookies, $cookiesCategories),
			'bodyAppendScripts' => $this->getScripts($website, $api, 'body-append', $cookies, $cookiesCategories),
			'reload' => $reload
		]);
	}

	/**
	 * Remove too old data
	 *
	 * @Route("/remove/old/data", methods={"DELETE", "GET"}, name="front_gdpr_remove_data", options={"expose"=true})
	 *
	 * @param Request $request
	 * @param GdprService $gdprService
	 * @return JsonResponse|RedirectResponse
	 * @throws Exception
	 */
	public function removeData(Request $request, GdprService $gdprService)
	{
		$gdprService->removeData($this->getWebsite($request));

		if ($request->get('referer')) {
			$session = new Session();
			$session->getFlashBag()->add('success', $this->translator->trans('Les données ont été supprimées avec succès.', [], 'admin'));
		}

		return new JsonResponse(['success' => true, 'reload' => true]);
	}

    /**
     * Get Scripts to inject in view
     *
     * @param array $website
     * @param array $api
     * @param string $dirname
     * @param array $cookies
     * @param array $cookiesCategories
     * @return string
     */
	private function getScripts(array $website, array $api, string $dirname, array $cookies, array $cookiesCategories): string
	{
		$fileSystem = new Filesystem();
		$projectDir = $this->kernel->getProjectDir();
		$scripts = '';

		foreach ($cookiesCategories as $category) {

			foreach ($category['gdprgroups'] as $group) {

                $slug = $group['slug'];
				$active = isset($cookies[$slug]) && $cookies[$slug]
					|| isset($cookies[$slug]) && !$cookies[$slug] && $group['anonymize']
					|| !isset($cookies[$slug]) && $group['anonymize'];

				if ($active) {

					$scriptTemplate = 'gdpr/' . $dirname . '/' . $slug . '.html.twig';
					$status = isset($cookies[$slug]) && $cookies[$slug];

					if ($fileSystem->exists($projectDir . '/templates/' . $scriptTemplate)) {
						$scripts .= $this->renderView($scriptTemplate, [
							'status' => $status,
							'code' => $slug,
							'website' => $website,
							'api' => $api
						]);
					}
				}
			}
		}

		return $scripts;
	}

	/**
	 * Get Cookies
	 *
	 * @param $cookiesRequest
	 * @return array
	 */
	private function getCookies($cookiesRequest): array
	{
		$cookies = [];
		$serializer = new Serializer([new GetSetMethodNormalizer()], ['json' => new JsonEncoder()]);

		if (!empty($cookiesRequest)) {
			$cookiesRequest = $serializer->decode($cookiesRequest, 'json');
			foreach ($cookiesRequest as $cookie) {
				$cookies[$cookie['slug']] = $cookie['status'];
			}
		}

		return $cookies;
	}
}