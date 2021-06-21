<?php

namespace App\Controller\Front;

use App\Controller\BaseController;
use App\Entity\Core\Configuration;
use App\Entity\Core\Website;
use App\Entity\Security\User;
use App\Service\Core\CacheService;
use DateTime;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * CacheController
 *
 * Manage render cache
 *
 * @property int CACHE_EXPIRES
 * @property string CHARSET
 *
 * @property Website $website
 * @property CacheService $cacheService
 * @property Configuration $configuration
 * @property Session $session
 * @property mixed $expiration
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CacheController extends BaseController
{
    private const CACHE_EXPIRES = 3600;
    private const CHARSET = 'UTF-8';

    private $website;
    private $cacheService;
    private $configuration;
    private $session;
    private $expiration;

    /**
     * Get cache
     *
     * @param string $template
     * @param mixed|null $entity
     * @param array $arguments
     * @param bool $htmlCache
     * @return JsonResponse|Response|string
     * @throws Exception
     */
    protected function cache(string $template, $entity = NULL, array $arguments = [], bool $htmlCache = false)
    {
        $this->cacheService = $this->subscriber->get(CacheService::class);
        $this->website = !empty($arguments['website']) ? $arguments['website'] : $this->getWebsite($this->request);

        if(!empty($arguments['interface']['name'])) {
            $arguments['interfaceName'] = $arguments['interface']['name'];
        }

        if ($htmlCache && $this->request->get('scroll-ajax') && $this->request->get('ajax')) {
            return new Response($this->cacheService->setCacheFile($this->website, $template, $arguments, $entity));
        }

        $this->session = new Session();
        $this->configuration = !empty($arguments['configuration']) && $arguments['configuration'] instanceof Configuration ? $arguments['configuration'] : $this->website->getConfiguration();
        $this->expiration = $this->getCacheExpires();

        return $this->cacheControl($template, $entity, $arguments);
    }

	/**
	 * Get master Request cache
	 *
	 * @param Website $website
	 * @param null $entity
	 * @return null|Response
	 */
    protected function masterCache(Website $website, $entity = NULL): ?Response
	{
        if ($this->getUser() instanceof User) {
            return NULL;
        }

        $this->website = $website;
        $this->cacheService = $this->subscriber->get(CacheService::class);
        $this->session = new Session();

        $isComputeEtag = $this->cacheService->isComputeETag($entity);
        $updatedAt = $this->cacheService->getUpdatedAt($entity);
        $asCache = $isComputeEtag && !$this->request->get('scroll-ajax');

        if ($asCache) {
            $response = new Response();
            $response->setETag($this->cacheService->setETag($isComputeEtag, $response, $entity));
            $response->setPublic();
            $response->setLastModified($updatedAt);
            if ($response->isNotModified($this->request)) {
                return $response;
            }
        }

        return NULL;
    }

    /**
     * Set Cache Control
     *
     * @param string $template
     * @param null $entity
     * @param array $arguments
     * @return JsonResponse|Response|null
     * @throws Exception
     */
    protected function cacheControl(string $template, $entity = NULL, array $arguments = [])
    {
        $isComputeEtag = $this->cacheService->isComputeETag($entity);
        $updatedAt = $this->cacheService->getUpdatedAt($entity);
        $website = !empty($arguments['website']) ? $arguments['website']
            : $this->entityManager->getRepository(Website::class)->findOneByHost($this->request->getHost());
        $configuration = $website instanceof Website ? $website->getConfiguration() : NULL;
        $userBackIPS = $configuration instanceof Configuration ? $website->getConfiguration()->getAllIPS() : [];

        $arguments['isUserBack'] = in_array(@$_SERVER['REMOTE_ADDR'], $userBackIPS) || $this->getUser() instanceof User;

        if ($this->request->get('scroll-ajax')) {
            return new JsonResponse(['html' => $this->renderView($template, $arguments), $this->expiration->result]);
        }

        if ($this->getUser() instanceof User) {
            return $this->render($template, $arguments);
        }

        $response = $this->getResponse($isComputeEtag, $template, $arguments);
        $response->setETag($this->cacheService->setETag($isComputeEtag, $response, $entity));
        $response->setPublic();
        $response->setExpires($this->expiration->date);
        $response->setCharset($this->getCharset());

        if ($isComputeEtag) {
            $response->setLastModified($updatedAt);
            if ($response->isNotModified($this->request) && !$this->getUser() instanceof User) {
                return $response;
            }
            return $this->render($template, $arguments, $response);
        } else {
            $response->isNotModified($this->request);
            return $response;
        }
    }

    /**
     * Get Response
     *
     * @param bool $isComputeEtag
     * @param string $template
     * @param array $arguments
     * @return Response
     * @throws Exception
     */
    public function getResponse(bool $isComputeEtag, string $template, array $arguments = []): ?Response
	{
        if (!$this->request->get('scroll-ajax')) {

            if ($isComputeEtag) {
                return new Response();
            } else {
                return $this->render($template, $arguments);
            }
        }

        return NULL;
    }

    /**
     * Get cache expiration
     *
     * @return object
     * @throws Exception
     */
    private function getCacheExpires()
    {
        $cacheConfiguration = $this->configuration ? $this->configuration->getCacheExpiration() : NULL;
        $cacheExpires = $cacheConfiguration ?: self::CACHE_EXPIRES;

        $date = new DateTime();
        $date->modify('+' . $cacheExpires . ' seconds');

        return (object)[
            'result' => $cacheExpires,
            'date' => $date
        ];
    }

    /**
     * Get Charset
     *
     * @return string
     */
    private function getCharset(): string
    {
        return $this->configuration->getCharset() ?: self::CHARSET;
    }

    /**
     * Get Cache file
     *
     * @param string $template
     * @param Configuration|null $configuration
     * @param mixed|null $entity
     * @return Response
     */
    protected function cacheFile(string $template, Configuration $configuration = NULL, $entity = NULL)
    {
        $this->cacheService = $this->subscriber->get(CacheService::class);
        return $this->cacheService->cacheFile($configuration, $template, $entity);
    }
}