<?php

namespace App\Service\Core;

use App\Entity\Core\Configuration;
use App\Entity\Core\Website;
use App\Entity\Layout\Layout;
use App\Entity\Security\User;
use App\Twig\Content\BrowserRuntime;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * CacheService
 *
 * Manage app cache
 *
 * @property AdapterInterface $cache
 * @property BrowserRuntime $browserRuntime
 * @property LoggerInterface $logger
 * @property bool $isMasterRequest
 * @property Request $request
 * @property Request $currentRequest
 * @property KernelInterface $kernel
 * @property Environment $twig
 * @property EntityManagerInterface $entityManager
 * @property bool $isDebug
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CacheService
{
	private $cache;
	private $browserRuntime;
	private $logger;
	private $isMasterRequest;
	private $request;
	private $currentRequest;
	private $kernel;
	private $twig;
	private $entityManager;
	private $isDebug;

	/**
	 * CacheService constructor.
	 *
	 * @param AdapterInterface $cache
	 * @param BrowserRuntime $browserRuntime
	 * @param LoggerInterface $cacheHelperLogger
	 * @param RequestStack $requestStack
	 * @param KernelInterface $kernel
	 * @param Environment $twig
	 * @param EntityManagerInterface $entityManager
	 * @param bool $isDebug
	 */
	public function __construct(
		AdapterInterface $cache,
		BrowserRuntime $browserRuntime,
		LoggerInterface $cacheHelperLogger,
		RequestStack $requestStack,
		KernelInterface $kernel,
		Environment $twig,
		EntityManagerInterface $entityManager,
		bool $isDebug)
	{
		$this->cache = $cache;
		$this->browserRuntime = $browserRuntime;
		$this->logger = $cacheHelperLogger;
		$this->isMasterRequest = $requestStack->getParentRequest() === NULL;
		$this->request = $requestStack->getMasterRequest();
		$this->currentRequest = $requestStack->getCurrentRequest();
		$this->kernel = $kernel;
		$this->twig = $twig;
		$this->entityManager = $entityManager;
		$this->isDebug = $isDebug;
	}

    /**
     * Set cache file
     *
     * @param Website $website
     * @param string $template
     * @param array $arguments
     * @param mixed|null $entity
     * @return string|null
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function setCacheFile(Website $website, string $template, array $arguments, $entity = NULL): ?string
    {
        $cache = $this->validateCache($website, $template, $entity);
        $finder = $cache && !empty($cache['finder']) && $cache['finder'] instanceof Finder ? $cache['finder'] : NULL;

        if ($cache && $cache['allowed'] && ($finder && $finder->count() > 0 || !$cache['fileExist'])) {
            try {
                $filesystem = new Filesystem();
                if (!$filesystem->exists($cache['cacheDirname'])) {
                    $filesystem->mkdir($cache['cacheDirname'], 0777);
                }
                file_put_contents($cache['cacheDirname'] . '/' . $cache['filename'], $this->twig->render($template, $arguments));
            } catch (Exception $exception) {
                return $this->twig->render($template, $arguments);
            }
        }

        return $this->twig->render($template, $arguments);
    }

    /**
     * Get Cache file
     *
     * @param Configuration|null $configuration
     * @param string|null $template
     * @param mixed|null $entity
     * @return Response|null
     */
    public function cacheFile(Configuration $configuration = NULL, string $template = NULL, $entity = NULL): ?Response
    {
        if (!$configuration || $configuration->getFullCache()) {

            try {

                $website = !$configuration instanceof Configuration && is_object($this->request) && method_exists($this->request, 'getHost')
                    ? $this->entityManager->getRepository(Website::class)->findOneByHost($this->request->getHost())
                    : ($configuration instanceof Configuration ? $configuration->getWebsite() : NULL);

                if ($website instanceof Website) {
                    $cache = $this->validateCache($website, $template, $entity);
                    $finder = $cache && !empty($cache['finder']) && $cache['finder'] instanceof Finder ? $cache['finder'] : NULL;
                    if ($finder && $finder->count() === 0) {
                        $finder = new Finder();
                        foreach ($finder->in($cache['cacheDirname'])->name($cache['filename']) as $file) {
                            /** @var SplFileInfo $file */
                            return new Response($file->getContents());
                        }
                    }
                }

            } catch (Exception $exception) {
            }
        }
        return NULL;
    }

    /**
     * Check if entity is as compute Etag
     *
     * @param mixed|null $entity
     * @return bool
     */
    public function isComputeETag($entity = NULL): bool
    {
        return $entity && method_exists($entity, 'getComputeETag') && !$this->isDebug;
    }

    /**
     * Check if entity is as compute Etag
     *
     * @param mixed|null $entity
     * @return DateTime|null
     */
    public function getUpdatedAt($entity = NULL): ?DateTime
    {
        if ($entity && method_exists($entity, 'getCreatedAt')
            && method_exists($entity, 'getUpdatedAt')) {
            return $entity->getUpdatedAt() ? $entity->getUpdatedAt() : $entity->getCreatedAt();
        }
        return NULL;
    }

	/**
	 * Check if entity is as compute Etag
	 *
	 * @param mixed|null $entity
	 * @return DateTime|null
	 */
	public function getLayoutUpdatedAt($entity = NULL): ?DateTime
	{
		/** @var Layout $layout */
		$layout = $entity->getLayout() instanceof Layout ? $entity->getLayout() : NULL;
		$updatedAt = NULL;

		if ($layout instanceof Layout) {
			$updatedAt = $entity->getUpdatedAt() ? $entity->getUpdatedAt() : $entity->getCreatedAt();
		}

		return $updatedAt;
	}

    /**
     * Check if entity is as compute Etag
     *
     * @param bool $isComputeEtag
     * @param string|Response $response
     * @param mixed|null $entity
     * @return string
     */
    public function setETag(bool $isComputeEtag, $response, $entity = NULL): ?string
    {
        if ($isComputeEtag) {
            $etag = $this->getComputeETag($entity);
        } else {
            $etag = md5($response->getContent());
        }

        return $etag;
    }

    /**
     * Get compute Etag
     *
     * @param $entity
     * @return string|null
     */
    private function getComputeETag($entity): ?string
    {
        if (method_exists($entity, 'getComputeETag')
            && method_exists($entity, 'setComputeETag')
            && method_exists($entity, 'getId')
            && method_exists($this->request, 'getUri')) {

            if (!$entity->getComputeETag() && !preg_match('/\/components/', $this->request->getUri())) {
                $entity->setComputeETag(uniqid() . md5($entity->getId()));
                $this->entityManager->persist($entity);
                $this->entityManager->flush();
            }

            return $entity->getComputeETag();
        }

        return NULL;
    }

	/**
	 * Parse
	 *
	 * @param string $source
	 * @param int|null $cacheExpires
	 * @return string
	 * @throws InvalidArgumentException
	 */
	public function parse(string $source, int $cacheExpires = NULL): string
	{
		if ($this->isDebug) {
			return $source;
		}

		try {

			$screen = $this->browserRuntime->screen();
			$item = $this->cache->getItem($screen . '-html-' . md5($source));

			if (!$item->isHit()) {
				$item->set($source);
				if ($cacheExpires) {
					$item->expiresAfter($cacheExpires);
				}
				$this->cache->save($item);
			}

			return $item->get();

		} catch (Exception $exception) {
			$this->logger->error($exception->getMessage() . ' at line ' . $exception->getLine());
		}

		return $source;
	}

    /**
     * Check if cache file existing
     *
     * @param Website $website
     * @param array $interface
     * @param string $type
     * @return object
     */
    public function cacheFileInfos(Website $website, array $interface, string $type)
    {
        $fileSystem = new Filesystem();
        $matches = explode('/', $this->request->getRequestUri());
        $uri = end($matches);
        $filename = $uri && $uri !== '/' ? $uri . '-' . $this->browserRuntime->screen() . '.html' : 'index' . '-' . $this->browserRuntime->screen() . '.html';
        $cacheDirname = $this->kernel->getCacheDir() . '/' . $website->getUploadDirname() . '/' . $type . '/' . $this->request->getLocale() . '/' . $interface['name'];
        $cacheFileDirname = $cacheDirname . '/' . $filename;

        if (!$fileSystem->exists($cacheDirname)) {
            $fileSystem->mkdir($cacheDirname, 0700);
        }

        return (object)[
            'cacheFileDirname' => $cacheFileDirname,
            'exists' => $fileSystem->exists($cacheFileDirname)
        ];
    }

	/**
	 * Clear Html files cache
	 *
	 * @param Website $website
	 */
	public function clearFrontCache(Website $website)
	{
		$filesystem = new Filesystem();
		$cacheDirNames = [
			$this->kernel->getProjectDir() . '/var/cache/prod/website/' . $website->getUploadDirname(),
		];

		foreach ($cacheDirNames as $cacheDirname) {
			$cacheDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $cacheDirname);
			if ($filesystem->exists($cacheDirname)) {
				$finder = new Finder();
				$finder->in($cacheDirname);
				foreach ($finder as $file) {
					if (!is_dir($file)) {
						$filesystem->remove($file);
					}
				}
			}
		}
	}

	/**
	 * Get cache validation
	 *
	 * @param Website $website
	 * @param string|null $template
	 * @param mixed|null $entity
	 * @return array|null
	 */
	private function validateCache(Website $website, string $template = NULL, $entity = NULL)
	{
		if (is_object($this->request) && method_exists($this->request, 'getLocale')) {

			$container = $this->kernel->getContainer();
			$token = $container->get('security.token_storage')->getToken();
			$user = method_exists($token, 'getUser') && method_exists($token->getUser(), 'getId') ? $token->getUser() : NULL;

			$filesystem = new Filesystem();
			$cacheDirname = $this->kernel->getCacheDir() . '/website/' . $website->getUploadDirname() . '/' . $this->request->getLocale() . '/' . $this->browserRuntime->screen();
			$filename = str_replace(['.twig', '/'], ['', '-'], $template);
			if (is_object($entity) && method_exists($entity, 'getId')) {
				$filename = $entity->getId() . '-' . ltrim(str_replace(['App\\Entity', 'App/Entity', '/', '\\'], ['', '', '-', '-'], get_class($entity)), '-') . '.html';
			}

			$response['cacheDirname'] = $cacheDirname;
			$response['filename'] = strtolower($filename);
			$response['website'] = $website;
			$response['fileExist'] = $filesystem->exists($cacheDirname . '/' . $filename);

			$response['allowed'] = !$user instanceof User
				&& !$this->request->isMethod('post') && !$this->currentRequest->isMethod('post')
				&& !preg_match('/\/preview\//', $this->request->getUri()) && !preg_match('/\/preview\//', $this->currentRequest->getUri());
			if ($this->isDebug && $response['allowed']) {
				$response['allowed'] = $website->getConfiguration()->getFullCacheDev();
			}

			$response['valid'] = $response['allowed'] && $response['fileExist'];
			if ($response['valid']) {
				$finder = new Finder();
				$expiration = $website->getConfiguration()->getCacheExpiration();
				$response['finder'] = $finder->in($cacheDirname)->date('before ' . $expiration . ' minute ago')->name($filename);
				return $response;
			}

			return $response;
		}
	}
}