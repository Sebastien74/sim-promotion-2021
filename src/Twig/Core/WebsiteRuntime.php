<?php

namespace App\Twig\Core;

use App\Entity\Core\Module;
use App\Entity\Core\Website;
use App\Entity\Information\SocialNetwork;
use App\Entity\Security\User;
use App\Helper\Core\InterfaceHelper;
use App\Repository\Core\DomainRepository;
use App\Repository\Core\WebsiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * WebsiteRuntime
 *
 * @property Request $request
 * @property WebsiteRepository $websiteRepository
 * @property DomainRepository $domainRepository
 * @property EntityManagerInterface $entityManager
 * @property InterfaceHelper $interfaceHelper
 * @property AuthorizationCheckerInterface $authorizationChecker
 * @property TokenStorageInterface $tokenStorage
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class WebsiteRuntime implements RuntimeExtensionInterface
{
    private $request;
    private $websiteRepository;
    private $domainRepository;
    private $entityManager;
    private $interfaceHelper;
    private $authorizationChecker;
    private $tokenStorage;

    /**
     * WebsiteRuntime constructor.
     *
     * @param RequestStack $requestStack
     * @param WebsiteRepository $websiteRepository
     * @param DomainRepository $domainRepository
     * @param EntityManagerInterface $entityManager
     * @param InterfaceHelper $interfaceHelper
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        RequestStack $requestStack,
        WebsiteRepository $websiteRepository,
        DomainRepository $domainRepository,
        EntityManagerInterface $entityManager,
        InterfaceHelper $interfaceHelper,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage)
    {
        $this->request = $requestStack->getMasterRequest();
        $this->websiteRepository = $websiteRepository;
        $this->domainRepository = $domainRepository;
        $this->entityManager = $entityManager;
        $this->interfaceHelper = $interfaceHelper;
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
    }

	/**
	 * Get current website
	 *
	 * @param bool $force
	 * @param bool $hasArray
	 * @return Website|array|null
	 * @throws NonUniqueResultException
	 */
    public function website(bool $force = false, bool $hasArray = false)
    {
        if ($this->request) {

            if (preg_match('/\/admin-' . $_ENV['SECURITY_TOKEN'] . '/', $this->request->getUri())) {

                $website = !$hasArray ? $this->request->getSession()->get('adminWebsite') : NULL;
                $websiteRequest = $this->request->get('website');

                if (!$website && $websiteRequest && is_numeric($websiteRequest)) {
                    $website = $hasArray ? $this->websiteRepository->findArray($websiteRequest) : $this->websiteRepository->find($websiteRequest);
                } elseif (!$website || $force) {
                    $website = $this->websiteRepository->findOneByHost($this->request->getHost(), false, $hasArray);
                }
            } else {

                $website = $this->request->getSession()->get('frontWebsite');
                if (!$website || !empty($_POST) || $force) {
                    $website = $this->websiteRepository->findOneByHost($this->request->getHost(), false, $hasArray);
                }
            }

            return $website;
        }

        return NULL;
    }

    /**
     * Get current Website request ID
     *
     * @return integer|NULL
     * @throws NonUniqueResultException
     */
    public function websiteId()
    {
        $website = $this->website();

        if (is_object($website)) {
            return $website->getId();
        } elseif (is_numeric($website)) {
            return $website;
        }

        return NULL;
    }

    /**
     * Get default domain name by locale
     *
     * @param string $locale
     * @param mixed $website
     * @return bool|string
     * @throws NonUniqueResultException
     */
    public function domain(string $locale, $website = NULL)
    {
        $protocol = $_ENV['PROTOCOL_' . strtoupper($_ENV['APP_ENV_NAME'])] . '://';
        $website = $website instanceof Website ? $website : (is_array($website) ? $website : $this->website());
        $configuration = $website instanceof Website ? $website->getConfiguration() : $website['configuration']['id'];
        $domains = $this->domainRepository->findBy([
            'configuration' => $configuration,
            'hasDefault' => true
        ]);

        $defaultDomain = false;

        foreach ($domains as $domain) {
            if ($domain->getLocale() === $locale) {
                return $protocol . $domain->getName();
            }
            if ($domain->getLocale() === $configuration->getLocale()) {
                $defaultDomain = $protocol . $domain->getName();
            }
        }

        return $defaultDomain;
    }

    /**
     * Get entity interface
     *
     * @param string|null $classname
     * @return array
     */
    public function interface(string $classname): array
    {
        $this->interfaceHelper->setInterface($classname);
        return $this->interfaceHelper->getInterface();
    }

    /**
     * Get entity interface name
     *
     * @param mixed $class
     * @return string|null
     */
    public function interfaceName($class = NULL): ?string
    {
        $classname = $class;
        if(is_object($class)) {
            $classname = get_class($class);
        }

        if ($classname) {
            $entity = new $classname();
            if (method_exists($entity, 'getInterface')) {
                return !empty($entity::getInterface()['name']) ? $entity::getInterface()['name'] : NULL;
            }
        }

        return NULL;
    }

    /**
     * Get modules status
     *
     * @param mixed $website
     * @return array
     */
    public function modules($website = NULL): array
    {
        if (!$website) {
            return [];
        }

        $modulesActives = [];
        $currentUser = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : NULL;
		$modulesDb = $this->entityManager->getRepository(Module::class)->findAllArray();
        $isInternal = $currentUser instanceof User && $this->authorizationChecker->isGranted('ROLE_INTERNAL');

        if ($currentUser) {
        	$configuration = is_object($website) ? $website->getConfiguration() : $website['configuration'];
			$modules = is_object($website) ? $configuration->getModules() : $configuration['modules'];
            foreach ($modules as $module) {
				$slug = is_object($website) ? $module->getSlug() : $module['slug'];
				$role = is_object($website) ? $module->getRole() : $module['role'];
                $modulesActives[$slug] = $this->authorizationChecker->isGranted($role);
            }
        }

        $modules = [];
        foreach ($modulesDb as $module) {
            if (!empty($modulesActives[$module['slug']])) {
                $modules[$module['slug']] = $modulesActives[$module['slug']];
            }
        }

        $modules['delete'] = $isInternal || $currentUser && $this->authorizationChecker->isGranted('ROLE_DELETE');
        $modules['gdpr'] = isset($modulesActives['gdpr']);

        ksort($modules);

        return $modules;
    }

    /**
     * Check if module is active
     *
     * @param string $moduleCode
     * @param array $allModules
     * @param bool $object
     * @return string|bool
     */
    public function moduleActive(string $moduleCode, $allModules = [], $object = false)
    {
        if (isset($allModules[$moduleCode])) {
            return $object ? $this->entityManager->getRepository(Module::class)->findOneBy(['slug' => $moduleCode]) : $allModules[$moduleCode];
        }

        return false;
    }

	/**
	 * Get Website social networks
	 *
	 * @param mixed $website
	 * @param string|null $locale
	 * @return array
	 */
    public function socialNetworks($website = NULL, string $locale = NULL): array
    {
        if (!$website) {
            return [];
        }

        $session = new Session();

        $websiteId = $website instanceof Website ? $website->getId() : $website['id'];
        $socialNetworksSession = $session->get('social_networks_' . $websiteId);
        if ($socialNetworksSession) {
            return $socialNetworksSession;
        }

        $result = [];
        $locale = !$locale ? $this->request->getLocale() : $locale;
        $informationId = $website instanceof Website ? $website->getInformation()->getId() : $website['information']['id'];
        $socialNetworks = $this->entityManager->getRepository(SocialNetwork::class)->findAllArray($informationId);

        foreach ($socialNetworks as $socialNetwork) {
            if ($socialNetwork['locale'] === $locale) {
                $result['twitter'] = $socialNetwork['twitter'];
                $result['facebook'] = $socialNetwork['facebook'];
                $result['google-plus'] = $socialNetwork['google'];
                $result['youtube'] = $socialNetwork['youtube'];
                $result['instagram'] = $socialNetwork['instagram'];
                $result['linkedin'] = $socialNetwork['linkedin'];
                $result['pinterest'] = $socialNetwork['pinterest'];
                $result['tripadvisor'] = $socialNetwork['tripadvisor'];
            }
        }

        $session->set('social_networks_' . $website['id'], $result);

        return $result;
    }

    /**
     * Get app colors by category
     *
     * @param Website $website
     * @param string $category
     * @return string
     */
    public function appColors(Website $website, string $category): string
    {
        $colors = '';

        foreach ($website->getConfiguration()->getColors() as $color) {
            if ($color->getCategory() === $category && $color->getIsActive()) {
                $colors .= $color->getColor() . ', ';
            }
        }

        return rtrim($colors, ', ');
    }

    /**
     * Get all websites
     *
     * @param bool $onlyActive
     * @return Website[]
     */
    public function allWebsites(bool $onlyActive = false): array
    {
        if ($onlyActive) {
            return $this->entityManager->getRepository(Website::class)->findBy(['active' => true]);
        }

        return $this->entityManager->getRepository(Website::class)->findAll();
    }

    /**
     * Check if is multi websites app
     *
     * @param bool $onlyActive
     * @return bool
     */
    public function isMultiWebsite(bool $onlyActive = false): bool
    {
        return count($this->allWebsites($onlyActive)) > 1;
    }
}