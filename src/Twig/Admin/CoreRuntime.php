<?php

namespace App\Twig\Admin;

use App\Entity\Core\Log;
use App\Entity\Core\Website;
use App\Entity\Layout\LayoutConfiguration;
use App\Entity\Security\Group;
use App\Entity\Security\User;
use App\Entity\Translation\i18n;
use App\Entity\Translation\Translation;
use App\Entity\Translation\TranslationDomain;
use App\Twig\Content\IconRuntime;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * CoreRuntime
 *
 * @property EntityManagerInterface $entityManager
 * @property AuthorizationCheckerInterface $authorizationChecker
 * @property IconRuntime $iconRuntime
 * @property RouterInterface $router
 * @property Environment $templating
 * @property Request $request
 * @property bool $editMode
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CoreRuntime implements RuntimeExtensionInterface
{
    private $entityManager;
    private $authorizationChecker;
    private $request;
    private $router;
    private $templating;
    private $iconRuntime;
    private $editMode = false;

    /**
     * CoreRuntime constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param RequestStack $requestStack
     * @param RouterInterface $router
     * @param Environment $templating
     * @param IconRuntime $iconRuntime
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        AuthorizationCheckerInterface $authorizationChecker,
        RequestStack $requestStack,
        RouterInterface $router,
        Environment $templating,
        IconRuntime $iconRuntime)
    {
        $this->entityManager = $entityManager;
        $this->authorizationChecker = $authorizationChecker;
        $this->router = $router;
        $this->request = $requestStack->getMasterRequest();
        $this->iconRuntime = $iconRuntime;
        $this->templating = $templating;
        $this->editMode = preg_match('/preview/', $this->request->getUri())
            && $this->request->get('edit_mode')
            && $this->authorizationChecker->isGranted('ROLE_EDIT');
    }

    /**
     * Check button display by User role
     * @return string
     */
    public function symfonyVersion(): string
    {
        return Kernel::VERSION;
    }

    /**
     * Get BlockTypes & Actions groups
     *
     * @param LayoutConfiguration $configuration
     * @return array
     */
    public function blockTypesActionsGroups(LayoutConfiguration $configuration): array
    {
        $groups = [];
        $groups = $this->getBlockTypes($configuration, $groups);
        return $this->getModules($configuration, $groups);
    }

    /**
     * Get BlockTypes groups
     *
     * @param LayoutConfiguration $configuration
     * @param array $groups
     * @return array
     */
    private function getBlockTypes(LayoutConfiguration $configuration, array $groups = []): array
    {
        $layoutGroups = ['layout', 'form'];
        $done = [];

        foreach ($configuration->getBlockTypes() as $blockType) {

            $groupCategory = $blockType->getCategory();
            $groupCategory = preg_match('/layout-/', $groupCategory) ? 'layout' : $groupCategory;

            foreach ($layoutGroups as $group) {
                if ($group === $groupCategory && $blockType->getSlug() !== 'action') {
                    $groups['block'][$group][$blockType->getPosition()] = $blockType;
                    $done[] = $blockType->getId();
                    ksort($groups['block'][$group]);
                }
            }

            if (!in_array($blockType->getId(), $done) && $blockType->getSlug() !== 'action') {
                $groupName = $blockType->getDropdown() ? 'secondary' : 'main';
                $groups['block'][$groupName][$blockType->getPosition()] = $blockType;
                ksort($groups['block'][$groupName]);
            }
        }

        if (!empty($groups['block'])) {
            ksort($groups['block']);
        }

        return $groups;
    }

    /**
     * Get Modules groups
     *
     * @param LayoutConfiguration $configuration
     * @param array $groups
     * @return array
     */
    private function getModules(LayoutConfiguration $configuration, array $groups = []): array
    {
        foreach ($configuration->getModules() as $module) {
            foreach ($module->getActions() as $action) {
                $groupName = $action->getDropdown() ? 'secondary' : 'main';
                $groups['module'][$groupName][$action->getPosition()] = $action;
                ksort($groups['module'][$groupName]);
            }
        }

        if (!empty($groups['module'])) {
            ksort($groups['module']);
        }

        return $groups;
    }

    /**
     * To check if entity is allowed to show in index for current User
     *
     * @param $entity
     * @param bool $isInternal
     * @return bool
     */
    public function indexAllowed($entity, bool $isInternal): bool
    {
        if ($entity instanceof Group) {
            $isInternalGroup = false;
            foreach ($entity->getRoles() as $role) {
                if ($role->getName() === 'ROLE_INTERNAL') {
                    $isInternalGroup = true;
                    break;
                }
            }
            if ($isInternalGroup && !$isInternal) {
                return false;
            }
        } elseif ($entity instanceof User) {
            $isInternalUser = false;
            foreach ($entity->getRoles() as $role) {
                if ($role === 'ROLE_INTERNAL') {
                    $isInternalUser = true;
                    break;
                }
            }
            if ($isInternalUser && !$isInternal) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get season icon
     *
     * @return string|null
     * @throws Exception
     */
    public function seasonIcon(): ?string
    {
        $currentDate = new DateTime('now');
        $year = $currentDate->format('Y');

        if ($currentDate >= new DateTime($year . '-09-22') && $currentDate <= new DateTime($year . '-12-21')) {
            return 'fad fa-umbrella';
        } elseif ($currentDate >= new DateTime($year . '-12-22') && $currentDate <= new DateTime((intval($year) + 1) . '-03-19')) {
            return 'fad fa-hat-winter';
        } elseif ($currentDate >= new DateTime((intval($year) - 1) . '-12-22') && $currentDate <= new DateTime($year . '-03-19')) {
            return 'fad fa-hat-winter';
        } elseif ($currentDate >= new DateTime($year . '-03-20') && $currentDate <= new DateTime($year . '-06-19')) {
            return 'fad fa-flower';
        } elseif ($currentDate >= new DateTime($year . '-06-20') && $currentDate <= new DateTime($year . '-09-21')) {
            return 'fad fa-tree-palm';
        }

        return NULL;
    }

    /**
     * Get log alert
     *
     * @return boolean
     */
    public function logAlert(): bool
    {
        $lastLog = $this->entityManager->getRepository(Log::class)->findUnread();

        return !empty($lastLog);
    }

    /**
     * Init entity property if not existing
     *
     * @param mixed $entity
     * @param string $property
     * @return string
     */
    public function methodInit($entity, string $property): string
    {
        $getter = 'get' . ucfirst($property);

        return method_exists($entity, $getter) ? $property : 'id';
    }

    /**
     * Get front fonts
     *
     * @param Website $website
     * @return string
     */
    public function appAdminFonts(Website $website): string
    {
        $fonts = '';

        foreach ($website->getConfiguration()->getAdminFonts() as $font) {
            $fonts .= ucfirst($font) . ', ';
        }

        return rtrim($fonts, ', ');
    }

    /**
     * Check button display status
     *
     * @param string $route
     * @param mixed $entity
     * @param array $interface
     * @return bool
     */
    public function buttonChecker(string $route, $entity, array $interface = []): bool
    {
        if (empty($interface['buttonsChecker'][$route])) {
            return true;
        }

        $properties = explode('.', $interface['buttonsChecker'][$route]);
        $display = true;

        foreach ($properties as $property) {
            $getter = 'get' . ucfirst($property);
            if (is_object($entity) && method_exists($entity, $getter)) {
                $display = $entity->$getter();
                $entity = $entity->$getter();
            }
        }

        return $display;
    }

    /**
     * Check button display by User role
     *
     * @param string $route
     * @param array $interface
     * @return bool
     */
    public function buttonRoleChecker(string $route, array $interface = []): bool
    {
        if (empty($interface['rolesChecker'][$route])) {
            return true;
        }

        return $this->authorizationChecker->isGranted($interface['rolesChecker'][$route]);
    }

    /**
     * Translation edition mode
     *
     * @param string $content
     * @param i18n|null $i18n
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function editTranslation(string $content, i18n $i18n = NULL): string
    {
        if ($this->editMode && $this->authorizationChecker->isGranted('ROLE_TRANSLATION')) {

            /** @var Website $website */
            $website = $this->entityManager->getRepository(Website::class)->findOneByHost($this->request->getHost());
            $i18nDomains = ['t' => 'title', 'b' => 'body', 'i' => 'introduction'];
            $isI18n = $i18n instanceof i18n;
            $route = $i18n ? 'front_translation_edit_i18n' : 'front_translation_edit_text';
            $domainName = 'front_' . str_replace('-', '_', $website->getConfiguration()->getTemplate());
            $domain = $this->entityManager->getRepository(TranslationDomain::class)->findOneBy(['name' => $domainName]);

            if($domain instanceof TranslationDomain) {
                $translations = $this->entityManager->getRepository(Translation::class)->findByDomainAndContent($domain, $content, $this->request->getLocale());
                echo $this->templating->render('admin/page/translation/front-translation.html.twig', [
                    'id' => str_shuffle(uniqid() . md5(uniqid())),
                    'formAction' => $this->router->generate($route),
                    'content' => $content,
                    'key_name' => !empty($translations[0]) ? $translations[0]->getUnit()->getKeyName() : $content,
                    'locale' => $this->request->getLocale(),
                    'domain' => $domainName,
                ]);
                return '';
            }
        }

        return $content;
    }
}