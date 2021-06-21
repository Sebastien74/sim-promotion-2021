<?php

namespace App\Form\Type\Layout;

use App\Entity\Core\Configuration;
use App\Entity\Core\Module;
use App\Entity\Core\Website;
use App\Entity\Layout\Page;
use App\Form\Widget as WidgetType;
use App\Repository\Module\Menu\MenuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * PageType
 *
 * @property TranslatorInterface $translator
 * @property KernelInterface $kernel
 * @property bool $isInternalUser
 * @property bool $haveBackgroundsRole
 * @property EntityManagerInterface $entityManager
 * @property MenuRepository $menuRepository
 * @property Request $request
 * @property Website $website
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class PageType extends AbstractType
{
    private $translator;
    private $kernel;
    private $isInternalUser;
    private $haveBackgroundsRole;
    private $entityManager;
    private $menuRepository;
    private $request;
    private $website;

    /**
     * PageType constructor.
     *
     * @param TranslatorInterface $translator
     * @param KernelInterface $kernel
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param EntityManagerInterface $entityManager
     * @param MenuRepository $menuRepository
     * @param RequestStack $requestStack
     */
    public function __construct(
        TranslatorInterface $translator,
        KernelInterface $kernel,
        AuthorizationCheckerInterface $authorizationChecker,
        EntityManagerInterface $entityManager,
        MenuRepository $menuRepository,
        RequestStack $requestStack)
    {
        $this->translator = $translator;
        $this->kernel = $kernel;
        $this->isInternalUser = $authorizationChecker->isGranted('ROLE_INTERNAL');
        $this->haveBackgroundsRole = $this->isInternalUser || $authorizationChecker->isGranted('ROLE_BACKGROUND_PAGE');
        $this->entityManager = $entityManager;
        $this->menuRepository = $menuRepository;
        $this->request = $requestStack->getMasterRequest();
    }

    /**
     * {@inheritdoc}
     *
     * @throws NonUniqueResultException
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Page $page */
        $page = $builder->getData();
        $isNew = !$page->getId();
        $this->website = $options['website'];
        $secureModule = $this->entityManager->getRepository(Module::class)->findOneBy(['role' => 'ROLE_SECURE_PAGE']);
        $secureActive = $secureModule ? $this->entityManager->getRepository(Configuration::class)->moduleExist($this->website, $secureModule) : NULL;
        $mainMenu = $this->menuRepository->findMain($this->website, $this->request->getLocale());

        $adminName = new WidgetType\AdminNameType($this->translator);
        $adminName->add($builder, [
            'adminNameGroup' => $isNew ? 'col-md-4' : 'col-12',
            'class' => 'refer-code'
        ]);

        if (!$page->getInfill()) {

            if ($isNew) {

                $builder->add('parent', EntityType::class, [
                    'required' => false,
                    'display' => 'search',
                    'label' => $this->translator->trans('Page parente', [], 'admin'),
                    'placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
                    'class' => Page::class,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('p')
                            ->leftJoin('p.urls', 'u')
                            ->andWhere('p.website = :website')
                            ->andWhere('u.isArchived = :isArchived')
                            ->setParameter('website', $this->website)
                            ->setParameter('isArchived', false)
                            ->orderBy('p.adminName', 'ASC');
                    },
                    'choice_label' => function ($page) {
                        return strip_tags($page->getAdminName());
                    },
                    'attr' => ['group' => 'col-md-4']
                ]);
            }

            $templateClass = $isNew ? 'col-md-4' : (!$isNew && !$this->haveBackgroundsRole ? 'col-12' : 'col-md-6');
            $builder->add('template', Type\ChoiceType::class, [
                'label' => $this->translator->trans('Template', [], 'admin'),
                'display' => 'search',
                'choices' => $this->getTemplates($page),
                'attr' => ['group' => $templateClass, 'data-config' => true]
            ]);

            if (!$isNew) {
                $urls = new WidgetType\UrlsCollectionType($this->translator);
                $urls->add($builder, ['display_seo' => true]);
            }

            if ($isNew) {

                if ($mainMenu) {
                    $builder->add('inMenu', Type\CheckboxType::class, [
                        'required' => false,
                        'mapped' => false,
                        'display' => 'button',
                        'color' => 'outline-dark',
                        'label' => $this->translator->trans('Afficher dans le menu', [], 'admin'),
                        'attr' => ['group' => $secureActive ? 'col-md-4 text-center' : 'col-md-6 text-center', 'class' => 'w-100']
                    ]);
                }

                $builder->add('infill', Type\CheckboxType::class, [
                    'required' => false,
                    'display' => 'button',
                    'color' => 'outline-dark',
                    'label' => $this->translator->trans('Page intercalaire', [], 'admin'),
                    'attr' => ['group' => $secureActive && $mainMenu ? 'col-md-4 text-center' : 'col-md-6 text-center', 'class' => 'w-100']
                ]);
            }

            if ($isNew && $secureActive) {
                $builder->add('isSecure', Type\CheckboxType::class, [
                    'required' => false,
                    'display' => 'button',
                    'color' => 'outline-dark',
                    'label' => $this->translator->trans('Page sécurisée', [], 'admin'),
                    'attr' => ['group' => !$mainMenu ? 'col-md-6 text-center' : 'col-md-4 text-center', 'class' => 'w-100']
                ]);
            }

            if (!$isNew) {

                $dates = new WidgetType\PublicationDatesType($this->translator);
                $dates->add($builder, [
                    'startGroup' => $this->haveBackgroundsRole || !$this->isInternalUser && !$this->haveBackgroundsRole ? 'col-12' : 'col-md-6',
                    'endGroup' => $this->haveBackgroundsRole || !$this->isInternalUser && !$this->haveBackgroundsRole ? 'col-12' : 'col-md-6',
                ]);

                if ($this->haveBackgroundsRole) {
                    $builder->add('backgroundColor', WidgetType\BackgroundColorSelectType::class, [
                        'label' => $this->translator->trans("Couleur de fond", [], 'admin'),
                        'attr' => [
                            'data-config' => true,
                            'class' => 'select-icons',
                            'group' => 'col-md-6'
                        ]
                    ]);
                }

                $builder->add('isIndex', Type\CheckboxType::class, [
                    'required' => false,
                    'display' => 'button',
                    'color' => 'outline-dark',
                    'label' => $this->translator->trans("Page d'accueil", [], 'admin'),
                    'attr' => ['data-config' => true, 'group' => $secureActive ? 'col-12' : 'col-md-6', 'class' => 'w-100']
                ]);

                $builder->add('infill', Type\CheckboxType::class, [
                    'required' => false,
                    'display' => 'button',
                    'color' => 'outline-dark',
                    'label' => $this->translator->trans('Page intercalaire', [], 'admin'),
                    'attr' => ['data-config' => true, 'group' => $secureActive ? 'col-12' : 'col-md-6', 'class' => 'w-100']
                ]);

                if ($secureActive) {
                    $builder->add('isSecure', Type\CheckboxType::class, [
                        'required' => false,
                        'display' => 'button',
                        'color' => 'outline-dark',
                        'label' => $this->translator->trans('Page sécurisée', [], 'admin'),
                        'attr' => ['data-config' => true, 'class' => 'w-100']
                    ]);
                }

                if ($this->haveBackgroundsRole) {
                    $mediaRelations = new WidgetType\MediaRelationsCollectionType($this->translator);
                    $mediaRelations->add($builder, [
                        'data_config' => true,
                        'entry_options' => ['onlyMedia' => true]
                    ]);
                }
            }
        } else {

            $builder->add('infill', Type\CheckboxType::class, [
                'required' => false,
                'label' => $this->translator->trans('Page intercalaire', [], 'admin'),
                'attr' => ['group' => 'col-md-6']
            ]);
        }

        $save = new WidgetType\SubmitType($this->translator);
        $save->add($builder);
    }

    /**
     * Get front templates
     *
     * @param Page $page
     * @return array
     */
    private function getTemplates(Page $page)
    {
        $componentsTemplate = 'components.html.twig';
        $disabledTemplates = ['error'];
        if (!$page->getIsIndex()) {
            $disabledTemplates[] = 'home';
        }

        $finder = new Finder();
        $templateDir = !$this->website ? "default" : $this->website->getConfiguration()->getTemplate();
        $templates = [];
        $frontDir = $this->kernel->getProjectDir() . '/templates/front/' . $templateDir . '/template/';
        $frontDir = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $frontDir);
        $finder->files()->in($frontDir)->depth([0]);

        foreach ($finder as $file) {
            if ($page->getTemplate() !== $componentsTemplate && $file->getFilename() !== $componentsTemplate
                || $page->getTemplate() === $componentsTemplate && $file->getFilename() === $componentsTemplate) {

                $code = str_replace('.html.twig', '', $file->getFilename());
                $templateName = $this->getTemplateName($code);

                if ($page->getIsIndex() && $code !== 'home') {
                    $disabledTemplates[] = $code;
                }

                if (!in_array($code, $disabledTemplates)) {
                    $templates[$templateName] = $file->getFilename();
                }
            }
        }

        return $templates;
    }

    private function getTemplateName(string $code)
    {
        $names = [
            'cms' => $this->translator->trans('Standard', [], 'admin'),
            'contact' => $this->translator->trans('Contact', [], 'admin'),
            'home' => $this->translator->trans('Accueil', [], 'admin'),
            'legacy' => $this->translator->trans('Mentions légales', [], 'admin'),
            'news' => $this->translator->trans("Index d'actualités", [], 'admin')
        ];

        return !empty($names[$code]) ? $names[$code] : ucfirst($code);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Page::class,
            'website' => NULL,
            'translation_domain' => 'admin',
            'allow_extra_fields' => true
        ]);
    }
}