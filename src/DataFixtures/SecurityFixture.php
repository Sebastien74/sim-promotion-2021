<?php

namespace App\DataFixtures;

use App\Entity\Security\Group;
use App\Entity\Security\Picture;
use App\Entity\Security\Role;
use App\Entity\Security\User;
use App\Service\Core\SubscriberService;
use Doctrine\Persistence\ObjectManager;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * SecurityFixture
 *
 * Security Fixture management
 *
 * @property array CUSTOMER_ROLES
 * @property array INTERNAL_ROLES
 * @property array TRANSLATOR_ROLES
 *
 * @property int $position
 * @property KernelInterface $kernel
 * @property User $createdBy
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SecurityFixture extends BaseFixture
{
    private const CUSTOMER_ROLES = [
        'ROLE_USER',
        'ROLE_ADMIN',
        'ROLE_ADD',
        'ROLE_EDIT',
        'ROLE_DELETE',
        'ROLE_PAGE',
        'ROLE_MEDIA',
        'ROLE_SEO',
        'ROLE_TRANSLATION'
    ];
    private const TRANSLATOR_ROLES = [
        'ROLE_USER',
        'ROLE_ADMIN',
        'ROLE_TRANSLATION',
        'ROLE_TRANSLATOR'
    ];

    private $position = 1;
    private $kernel;
    private $createdBy;

    /**
     * SecurityFixture constructor.
     *
     * @param TranslatorInterface $translator
     * @param SubscriberService $subscriber
     * @param KernelInterface $kernel
     */
    public function __construct(TranslatorInterface $translator, SubscriberService $subscriber, KernelInterface $kernel)
    {
        parent::__construct($translator, $subscriber);

        $this->kernel = $kernel;
    }

    /**
     * {@inheritDoc}
     */
    protected function loadData(ObjectManager $manager)
    {
        $this->manager = $manager;

        $this->addRoles();

        $createdBy = NULL;

        foreach ($this->getUsers() as $userConfig) {
            $user = $this->addUser($userConfig);
            $this->setPicture($user, $userConfig);
        }

        $this->manager->flush();
    }

    /**
     * Add Roles
     */
    private function addRoles()
    {
        $yamlRoles = $this->getYamlRoles();
        $position = 1;

        foreach ($yamlRoles as $roleName => $config) {

            $adminName = !empty($config['fr']) ? $config['fr'] : $roleName;

            $role = new Role();
            $role->setAdminName($adminName);
            $role->setName($roleName);
            $role->setSlug(Urlizer::urlize($roleName));
            $role->setPosition($position);

            $this->addReference($roleName, $role);

            $this->manager->persist($role);
            $position++;
        }
    }

    /**
     * Get Yaml Roles
     *
     * @param bool $onlyName
     * @return array
     */
    private function getYamlRoles(bool $onlyName = false): array
    {
        $securityDirname = $this->kernel->getProjectDir() . '/bin/data/fixtures/security.yaml';
        $securityDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $securityDirname);
        $yamlRoles = Yaml::parseFile($securityDirname);

        if ($onlyName) {
            $roles = [];
            foreach ($yamlRoles as $roleName => $config) {
                $roles[] = $roleName;
            }
            return $roles;
        }

        return $yamlRoles;
    }

    /**
     * Get Users configuration
     *
     * @return array
     */
    private function getUsers(): array
    {
        $users[] = [
            "markup" => "232",
            "email" => "support@felix-creation.fr",
            "login" => "webmaster",
            "roles" => $this->getYamlRoles(true),
            "lastname" => 'Agence Félix',
            "group" => $this->translator->trans('Interne', [], 'security'),
            "password" => '$2y$10$yzsckDg/ad8P/MiLzuOPCehisJDkLKfO45LB4u9KtUd.T.LDjFVTq',
            "code" => 'internal',
            "active" => true,
            "picture" => 'webmaster.svg'
        ];

        $users[] = [
            "markup" => "233",
            "email" => "customer@felix-creation.fr",
            "login" => "customer",
            "roles" => self::CUSTOMER_ROLES,
            "lastname" => $this->translator->trans('Administrateur', [], 'security'),
            "group" => $this->translator->trans('Administrateur', [], 'security'),
            "password" => '$2y$10$d7fMNRs1DspZZ9KYML4UQuGirin.2N1pgkxFG/tHNmP4e3pLAIlt2',
            "code" => 'administrator',
            "active" => true,
            "picture" => 'customer.png'
        ];

        $users[] = [
            "markup" => "234",
            "email" => "translator@felix-creation.fr",
            "login" => "translator",
            "roles" => self::TRANSLATOR_ROLES,
            "lastname" => $this->translator->trans('Traducteur', [], 'security'),
            "group" => $this->translator->trans('Traducteur', [], 'security'),
            "password" => '$2y$10$VGz4ZdbQLjT4gKzT7U3TOeJObWMl3WUjGQZi137HpiaqRcdYwzmbG',
            "code" => 'translator',
            "active" => false,
            "picture" => 'translator.png'
        ];

        return $users;
    }

    /**
     * Add User
     *
     * @param array $userConfig
     * @return User
     */
    private function addUser(array $userConfig): User
    {
        $userConfig = (object)$userConfig;

        $user = new User();
        $user->setEmail($userConfig->email);
        $user->setLogin($userConfig->login);
        $user->setLastname($userConfig->lastname);
        $user->setPassword($userConfig->password);
        $user->setActive($userConfig->active);
        $user->setLocale($this->locale);
        $user->setActive(true);
        $user->agreeTerms();

        if (property_exists($userConfig, 'firstname')) {
            $user->setFirstName($userConfig->firstname);
        }

        if (property_exists($userConfig, 'theme')) {
            $user->setTheme($userConfig->theme);
        }

        if ($user->getLogin() === 'webmaster') {
            $this->createdBy = $user;
        }

        $this->addReference($userConfig->login, $user);
        $this->addGroup((array)$userConfig, $user);

        $this->manager->persist($user);

        return $user;
    }

    /**
     * Set User Picture
     *
     * @param User $user
     * @param array $userConfig
     * @return User
     */
    private function setPicture(User $user, array $userConfig): User
    {
        $userConfig = (object)$userConfig;

        $picture = new Picture();
        $picture->setFilename($userConfig->picture);
        $picture->setDirname('/uploads/users/' . $userConfig->picture);
        $picture->setUser($user);

        $user->setPicture($picture);

        $this->manager->persist($user);

        return $user;
    }

    /**
     * Add Group
     *
     * @param array $userConfig
     * @param User $user
     */
    private function addGroup(array $userConfig, User $user)
    {
        $userConfig = (object)$userConfig;

        $group = new Group();
        $group->setAdminName($userConfig->group);
        $group->setSlug($userConfig->code);
        $group->setCreatedBy($this->createdBy);
        $group->setPosition($this->position);

        foreach ($userConfig->roles as $role) {
            /** @var Role $roleReference */
            $roleReference = $this->getReference($role);
            $group->addRole($roleReference);
        }

        $user->setGroup($group);

        $this->manager->persist($group);
        $this->position++;
    }
}