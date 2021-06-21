<?php

namespace App\Form\Widget;

use App\Entity\Core\Website;
use App\Entity\Layout\Page;
use App\Entity\Media\Folder;
use App\Entity\Translation\i18n;
use App\Repository\Core\WebsiteRepository;
use App\Repository\Media\FolderRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * i18nType
 *
 * @property TranslatorInterface $translator
 * @property bool $isInternalUser
 * @property Request $request
 * @property WebsiteRepository $websiteRepository
 * @property FolderRepository $folderRepository
 * @property array $options
 * @property Website $website
 * @property array $websites
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class i18nType extends AbstractType
{
    private const NAME = "i18n";

    private $translator;
    private $isInternalUser;
    private $request;
    private $websiteRepository;
    private $folderRepository;
    private $options = [];
    private $website;
    private $websites;

    /**
     * i18nType constructor.
     *
     * @param TranslatorInterface $translator
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param RequestStack $requestStack
     * @param WebsiteRepository $websiteRepository
     * @param FolderRepository $folderRepository
     */
    public function __construct(
        TranslatorInterface $translator,
        AuthorizationCheckerInterface $authorizationChecker,
        RequestStack $requestStack,
        WebsiteRepository $websiteRepository,
        FolderRepository $folderRepository)
    {
        $this->translator = $translator;
        $this->isInternalUser = $authorizationChecker->isGranted('ROLE_INTERNAL');
        $this->request = $requestStack->getMasterRequest();
        $this->websiteRepository = $websiteRepository;
        $this->folderRepository = $folderRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->options = $options;
        $this->website = $options['website'];
        $this->websites = $this->websiteRepository->findAll();
        if (!$this->website && $this->request->get('website')) {
            $this->website = $this->websiteRepository->find($this->request->get('website'));
        }

        $haveLink = false;
        foreach ($options['fields'] as $key => $name) {
            $field = is_int($key) ? $name : $key;
            if (preg_match('/target/', $field)) {
                $haveLink = true;
            }
        }

        foreach ($options['fields'] as $key => $name) {

            $field = is_int($key) ? $name : $key;
            $groupClass = is_int($key) ? 'col-12' : $name;
            $getter = 'get' . ucfirst($field);
            $isValid = !$this->options['target_config'] || $this->options['target_config'] && !$haveLink && $field === 'newTab' || $field !== 'newTab';

            if ($isValid && method_exists($this, $getter)) {
                $this->$getter($builder, $field, $groupClass);
            }
        }

        if ($haveLink && $this->options['target_config']) {
            $this->getTargetFields($builder);
        }
    }

    /**
     * Title field
     *
     * @param FormBuilderInterface $builder
     * @param string $field
     * @param string|null $groupClass
     */
    private function getTitle(FormBuilderInterface $builder, string $field, string $groupClass = NULL)
    {
        $isRequired = in_array($field, $this->options['required_fields']);
        $constraints = $this->getConstraints($isRequired);
        $fieldType = !empty($this->options['fields_type'][$field]) ? $this->options['fields_type'][$field] : Type\TextType::class;

        if ($this->options['content_config'] || $this->options['title_force']) {
            $builder->add('titleForce', Type\ChoiceType::class, [
                'required' => false,
                'display' => 'search',
                'label' => $this->getAttribute('titleForce', 'label'),
                'placeholder' => $this->getAttribute('titleForce', 'placeholder'),
                'attr' => ['group' => "col-md-2"],
                'choices' => ['H1' => 1, 'H2' => 2, 'H3' => 3, 'H4' => 4, 'H5' => 5, 'H6' => 6],
                'help' => $this->getAttribute('titleForce', 'help')
            ]);
        }

        $builder->add($field, $fieldType, [
            'required' => $isRequired,
            'label' => $this->getAttribute($field, 'label'),
            'attr' => [
                'placeholder' => $this->getAttribute($field, 'placeholder'),
                'group' => $groupClass === 'col-12' && ($this->options['content_config'] || $this->options['title_force']) ? "col-md-10" : $groupClass
            ],
            'constraints' => $constraints,
            'help' => $this->getAttribute($field, 'help')
        ]);

        if ($this->options['content_config'] && $this->isInternalUser || in_array('titleAlignment', $this->options['config_fields'])) {

            $builder->add('titleAlignment', Type\ChoiceType::class, [
                'required' => false,
                'display' => 'search',
                'label' => $this->getAttribute('titleAlignment', 'label'),
                'placeholder' => $this->getAttribute('titleAlignment', 'placeholder'),
                'attr' => [
                    'group' => isset($this->options['fields']['alignment']) ? $this->options['fields']['alignment'] : "col-md-4"
                ],
                'choices' => $this->getAlignments(),
                'help' => $this->getAttribute('titleAlignment', 'help')
            ]);
        }
    }

    /**
     * SubTitle field
     *
     * @param FormBuilderInterface $builder
     * @param string $field
     * @param string|null $groupClass
     */
    private function getSubTitle(FormBuilderInterface $builder, string $field, string $groupClass = NULL)
    {
        $isRequired = in_array($field, $this->options['required_fields']);
        $constraints = $this->getConstraints($isRequired);
        $fieldType = !empty($this->options['fields_type'][$field]) ? $this->options['fields_type'][$field] : Type\TextType::class;

        $builder->add($field, $fieldType, [
            'required' => $isRequired,
            'label' => $this->getAttribute($field, 'label'),
            'attr' => [
                'placeholder' => $this->getAttribute($field, 'placeholder'),
                'group' => !empty($this->options['fields'][$field]) ? $this->options['fields'][$field] : 'col-md-5'
            ],
            'constraints' => $constraints,
            'help' => $this->getAttribute($field, 'help')
        ]);

        if(in_array('subTitlePosition', $this->options['config_fields'])) {
            $isRequired = in_array('subTitlePosition', $this->options['required_fields']);
            $constraints = $this->getConstraints($isRequired);
            $builder->add('subTitlePosition', Type\ChoiceType::class, [
                'required' => $isRequired,
                'label' => $this->getAttribute('subTitlePosition', 'label'),
                'display' => 'search',
                'placeholder' => $this->getAttribute('subTitlePosition', 'placeholder'),
                'choices' => [
                    $this->translator->trans('Haut', [], 'admin') => 'top',
                    $this->translator->trans('Bas', [], 'admin') => 'bottom',
                ],
                'attr' => [
                    'group' => !empty($this->options['fields']['subTitlePosition']) ? $this->options['fields']['subTitlePosition'] : 'col-md-3'
                ],
                'constraints' => $constraints,
                'help' => $this->getAttribute($field, 'help')
            ]);
        }
    }

    /**
     * Introduction field
     *
     * @param FormBuilderInterface $builder
     * @param string $field
     * @param string|null $groupClass
     */
    private function getIntroduction(FormBuilderInterface $builder, string $field, string $groupClass = NULL)
    {
        $isRequired = in_array($field, $this->options['required_fields']);
        $constraints = $this->getConstraints($isRequired);
        $fieldType = !empty($this->options['fields_type'][$field]) ? $this->options['fields_type'][$field] : Type\TextareaType::class;

        $builder->add($field, $fieldType, [
            'required' => $isRequired,
            'editor' => preg_match('/editor/', $groupClass) ? 'summernote' : 'basic',
            'label' => $this->getAttribute($field, 'label'),
            'attr' => [
                'placeholder' => $this->getAttribute($field, 'placeholder'),
                'group' => $groupClass
            ],
            'constraints' => $constraints,
            'help' => $this->getAttribute($field, 'help')
        ]);

        if ($this->options['content_config'] && $this->isInternalUser || in_array('introductionAlignment', $this->options['config_fields'])) {
            $builder->add('introductionAlignment', Type\ChoiceType::class, [
                'required' => false,
                'display' => 'search',
                'label' => $this->getAttribute('introductionAlignment', 'label'),
                'placeholder' => $this->getAttribute('introductionAlignment', 'placeholder'),
                'attr' => [
                    'group' => isset($this->options['fields']['alignment']) ? $this->options['fields']['alignment'] : "col-md-4"
                ],
                'choices' => $this->getAlignments(),
                'help' => $this->getAttribute('introductionAlignment', 'help')
            ]);
        }
    }

    /**
     * Body field
     *
     * @param FormBuilderInterface $builder
     * @param string $field
     * @param string|null $groupClass
     */
    private function getBody(FormBuilderInterface $builder, string $field, string $groupClass = NULL)
    {
        $isRequired = in_array($field, $this->options['required_fields']);
        $constraints = $this->getConstraints($isRequired);
        $fieldType = !empty($this->options['fields_type'][$field]) ? $this->options['fields_type'][$field] : Type\TextareaType::class;
        $builder->add($field, $fieldType, [
            'required' => in_array($field, $this->options['required_fields']),
            'label' => $this->getAttribute($field, 'label'),
            'editor' => !preg_match('/no-editor/', $groupClass),
            'attr' => [
                'placeholder' => $this->getAttribute($field, 'placeholder'),
                'group' => $groupClass
            ],
            'help' => $this->getAttribute($field, 'help'),
            'constraints' => $constraints
        ]);

        if (!in_array('headerTable', $this->options['excludes_fields'])) {
            $builder->add('headerTable', Type\CheckboxType::class, [
                'required' => false,
                'display' => 'button',
                'color' => 'outline-dark',
                'label' => $this->getAttribute('headerTable', 'label'),
                'attr' => ['group' => 'col-md-4', 'class' => 'w-100'],
                'help' => $this->getAttribute('headerTable', 'help')
            ]);
        }

        if ($this->options['content_config'] && $this->isInternalUser || in_array('bodyAlignment', $this->options['config_fields'])) {
            $builder->add('bodyAlignment', Type\ChoiceType::class, [
                'required' => false,
                'display' => 'search',
                'label' => $this->getAttribute('bodyAlignment', 'label'),
                'placeholder' => $this->getAttribute('bodyAlignment', 'placeholder'),
                'attr' => [
                    'group' => isset($this->options['fields']['alignment']) ? $this->options['fields']['alignment'] : "col-md-4"
                ],
                'choices' => $this->getAlignments(),
                'help' => $this->getAttribute('bodyAlignment', 'help')
            ]);
        }
    }

    /**
     * Target Link field
     *
     * @param FormBuilderInterface $builder
     * @param string $field
     * @param string|null $groupClass
     */
    private function getTargetLink(FormBuilderInterface $builder, string $field, string $groupClass = NULL)
    {
        $builder->add($field, Type\TextType::class, [
            'required' => in_array($field, $this->options['required_fields']),
            'label' => $this->getAttribute($field, 'label'),
            'attr' => [
                'placeholder' => $this->getAttribute($field, 'placeholder'),
                'group' => !empty($this->options['fields'][$field]) ? $this->options['fields'][$field] : 'col-12'
            ],
            'help' => $this->getAttribute($field, 'help')
        ]);
    }

    /**
     * Target Link field
     *
     * @param FormBuilderInterface $builder
     * @param string $field
     * @param string|null $groupClass
     */
    private function getTargetPage(FormBuilderInterface $builder, string $field, string $groupClass = NULL)
    {
        $builder->add($field, EntityType::class, [
            'required' => in_array($field, $this->options['required_fields']),
            'label' => $this->getAttribute($field, 'label'),
            'display' => 'search',
            'class' => Page::class,
            'placeholder' => $this->getAttribute($field, 'placeholder'),
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('p')
                    ->leftJoin('p.urls', 'u')
                    ->leftJoin('p.website', 'w')
                    ->andWhere('p.infill = :infill')
                    ->andWhere('u.isOnline = :isOnline')
                    ->setParameter(':infill', false)
                    ->setParameter(':isOnline', true)
                    ->addSelect('u')
                    ->addSelect('w')
                    ->orderBy('p.adminName', 'ASC');
            },
            'choice_label' => function (Page $page) {
                return count($this->websites) > 1
                    ? strip_tags($page->getAdminName()) . ' ( ' . strip_tags($page->getWebsite()->getAdminName()) . ' )'
                    : strip_tags($page->getAdminName());
            },
            'attr' => ['group' => !empty($this->options['fields'][$field]) ? $this->options['fields'][$field] : 'col-md-6'],
            'help' => $this->getAttribute($field, 'help')
        ]);
    }

    /**
     * Target Link field
     *
     * @param FormBuilderInterface $builder
     * @param string $field
     * @param string|null $groupClass
     */
    private function getTargetLabel(FormBuilderInterface $builder, string $field, string $groupClass = NULL)
    {
        $builder->add($field, Type\TextType::class, [
            'required' => in_array($field, $this->options['required_fields']),
            'label' => $this->getAttribute($field, 'label'),
            'attr' => [
                'placeholder' => $this->getAttribute($field, 'placeholder'),
                'group' => !empty($this->options['fields'][$field]) ? $this->options['fields'][$field] : 'col-md-6'
            ],
            'help' => $this->getAttribute($field, 'help')
        ]);
    }

    /**
     * Target Link fields
     *
     * @param FormBuilderInterface $builder
     * @param string|null $groupClass
     */
    private function getTargetFields(FormBuilderInterface $builder, string $groupClass = NULL)
    {
        if (!in_array('targetAlignment', $this->options['excludes_fields']) && $this->isInternalUser
            || in_array('targetAlignment', $this->options['config_fields'])) {
            $builder->add('targetAlignment', Type\ChoiceType::class, [
                'required' => false,
                'display' => 'search',
                'label' => $this->getAttribute('targetAlignment', 'label'),
                'placeholder' => $this->getAttribute('targetAlignment', 'placeholder'),
                'attr' => [
                    'group' => !empty($this->options['fields']['targetAlignment']) ? $this->options['fields']['targetAlignment'] : 'col-md-6'
                ],
                'choices' => $this->getAlignments(),
                'help' => $this->getAttribute('targetAlignment', 'help')
            ]);
        }

        if (!in_array('targetStyle', $this->options['excludes_fields'])) {
            $builder->add('targetStyle', ButtonColorType::class, [
                'attr' => [
                    'class' => 'select-icons',
                    'group' => !empty($this->options['fields']['targetStyle']) ? $this->options['fields']['targetStyle'] : 'col-md-6'
                ],
            ]);
        }

        $this->getNewTab($builder, 'newTab');
    }

    /**
     * Placeholder field
     *
     * @param FormBuilderInterface $builder
     * @param string $field
     * @param string|null $groupClass
     */
    private function getPlaceholder(FormBuilderInterface $builder, string $field, string $groupClass = NULL)
    {
        $fieldType = !empty($this->options['fields_type'][$field]) ? $this->options['fields_type'][$field] : Type\TextType::class;
        $builder->add($field, $fieldType, [
            'required' => in_array($field, $this->options['required_fields']),
            'label' => $this->getAttribute($field, 'label'),
            'attr' => [
                'placeholder' => $this->getAttribute($field, 'placeholder'),
                'group' => !empty($this->options['fields'][$field]) ? $this->options['fields'][$field] : 'col-md-6'
            ],
            'help' => $this->getAttribute($field, 'help')
        ]);
    }

    /**
     * Author field
     *
     * @param FormBuilderInterface $builder
     * @param string $field
     * @param string|null $groupClass
     */
    private function getAuthor(FormBuilderInterface $builder, string $field, string $groupClass = NULL)
    {
        $fieldType = !empty($this->options['fields_type'][$field]) ? $this->options['fields_type'][$field] : Type\TextType::class;
        $builder->add($field, $fieldType, [
            'required' => in_array($field, $this->options['required_fields']),
            'label' => $this->getAttribute($field, 'label'),
            'attr' => [
                'placeholder' => $this->getAttribute($field, 'placeholder'),
                'group' => !empty($this->options['fields'][$field]) ? $this->options['fields'][$field] : 'col-md-3'
            ],
            'help' => $this->getAttribute($field, 'help')
        ]);
    }

    /**
     * AuthorType field
     *
     * @param FormBuilderInterface $builder
     * @param string $field
     * @param string|null $groupClass
     */
    private function getAuthorType(FormBuilderInterface $builder, string $field, string $groupClass = NULL)
    {
        $builder->add($field, Type\TextType::class, [
            'required' => in_array($field, $this->options['required_fields']),
            'label' => $this->getAttribute($field, 'label'),
            'attr' => [
                'placeholder' => $this->getAttribute($field, 'placeholder'),
                'group' => !empty($this->options['fields'][$field]) ? $this->options['fields'][$field] : 'col-md-3'
            ],
            'help' => $this->getAttribute($field, 'help')
        ]);
    }

    /**
     * Help field
     *
     * @param FormBuilderInterface $builder
     * @param string $field
     * @param string|null $groupClass
     */
    private function getHelp(FormBuilderInterface $builder, string $field, string $groupClass = NULL)
    {
        $fieldType = !empty($this->options['fields_type'][$field]) ? $this->options['fields_type'][$field] : Type\TextType::class;
        $builder->add($field, $fieldType, [
            'required' => in_array($field, $this->options['required_fields']),
            'label' => $this->getAttribute($field, 'label'),
            'attr' => [
                'placeholder' => $this->getAttribute($field, 'placeholder'),
                'group' => !empty($this->options['fields'][$field]) ? $this->options['fields'][$field] : 'col-md-6'
            ],
            'help' => $this->getAttribute($field, 'help')
        ]);
    }

    /**
     * Error field
     *
     * @param FormBuilderInterface $builder
     * @param string $field
     * @param string|null $groupClass
     */
    private function getError(FormBuilderInterface $builder, string $field, string $groupClass = NULL)
    {
        $fieldType = !empty($this->options['fields_type'][$field]) ? $this->options['fields_type'][$field] : Type\TextType::class;
        $builder->add($field, $fieldType, [
            'required' => in_array($field, $this->options['required_fields']),
            'label' => $this->getAttribute($field, 'label'),
            'attr' => [
                'placeholder' => $this->getAttribute($field, 'placeholder'),
                'group' => !empty($this->options['fields'][$field]) ? $this->options['fields'][$field] : 'col-md-6'
            ],
            'help' => $this->getAttribute($field, 'help')
        ]);
    }

    /**
     * New tab field
     *
     * @param FormBuilderInterface $builder
     * @param string $field
     * @param string|null $groupClass
     */
    private function getNewTab(FormBuilderInterface $builder, string $field, string $groupClass = NULL)
    {
        $groupClass = !empty($this->options['fields'][$field]) ? $this->options['fields'][$field] : 'col-12';
        $groupClass = !empty($this->options['groups_fields'][$field]) ? $this->options['groups_fields'][$field] : $groupClass;

        if (!in_array($field, $this->options['excludes_fields'])) {
            $builder->add($field, Type\CheckboxType::class, [
                'required' => in_array($field, $this->options['required_fields']),
                'display' => 'button',
                'color' => 'outline-dark',
                'label' => $this->getAttribute($field, 'label'),
                'attr' => ['group' => $groupClass, 'class' => 'w-100'],
                'help' => $this->getAttribute($field, 'help')
            ]);
        }

        $groupClass = !empty($this->options['groups_fields']['externalLink']) ? $this->options['groups_fields']['externalLink'] : $groupClass;

        if (!in_array('externalLink', $this->options['excludes_fields'])) {
            $builder->add('externalLink', Type\CheckboxType::class, [
                'required' => false,
                'display' => 'button',
                'color' => 'outline-dark',
                'label' => $this->translator->trans('Lien externe'),
                'attr' => ['group' => $groupClass, 'class' => 'w-100'],
                'help' => $this->getAttribute('externalLink', 'help')
            ]);
        }
    }

    /**
     * Active field
     *
     * @param FormBuilderInterface $builder
     * @param string $field
     * @param string|null $groupClass
     */
    private function getActive(FormBuilderInterface $builder, string $field, string $groupClass = NULL)
    {
        if (!in_array($field, $this->options['excludes_fields'])) {
            $groupClass = !empty($this->options['fields'][$field]) ? $this->options['fields'][$field] : 'col-12';
            $builder->add($field, Type\CheckboxType::class, [
                'required' => false,
                'display' => 'button',
                'color' => 'outline-dark',
                'label' => $this->getAttribute($field, 'label'),
                'attr' => ['group' => $groupClass, 'class' => 'w-100'],
                'help' => $this->getAttribute($field, 'help')
            ]);
        }
    }

    /**
     * New pictogram field
     *
     * @param FormBuilderInterface $builder
     * @param string $field
     * @param string|null $groupClass
     */
    private function getPictogram(FormBuilderInterface $builder, string $field, string $groupClass = NULL)
    {
        $builder->add($field, Type\ChoiceType::class, [
            'required' => in_array($field, $this->options['required_fields']),
            'label' => $this->getAttribute($field, 'label'),
            'choices' => $this->getPictograms($this->website),
            'choice_attr' => function ($dir, $key, $value) {
                return ['data-background' => strtolower($dir)];
            },
            'placeholder' => $this->translator->trans("Sélectionnez", [], 'admin'),
            'attr' => [
                'data-placeholder' => $this->translator->trans("Sélectionnez", [], 'admin'),
                'group' => !empty($this->options['fields'][$field]) ? $this->options['fields'][$field] : 'col-12',
                'class' => 'select-icons'
            ],
            'help' => $this->getAttribute($field, 'help')
        ]);
    }

    /**
     * Video field
     *
     * @param FormBuilderInterface $builder
     * @param string $field
     * @param string|null $groupClass
     */
    private function getVideo(FormBuilderInterface $builder, string $field, string $groupClass = NULL)
    {
        $isRequired = in_array($field, $this->options['required_fields']);
        $constraints = $this->getConstraints($isRequired);

        $builder->add($field, Type\TextType::class, [
            'required' => in_array($field, $this->options['required_fields']),
            'label' => $this->getAttribute($field, 'label'),
            'attr' => [
                'placeholder' => $this->getAttribute($field, 'placeholder'),
                'group' => !empty($this->options['fields'][$field]) ? $this->options['fields'][$field] : 'col-12'
            ],
            'help' => $this->getAttribute($field, 'help'),
            'constraints' => $constraints,
        ]);
    }

    /**
     * Target Slug field
     *
     * @param FormBuilderInterface $builder
     * @param string $field
     * @param string|null $groupClass
     */
    private function getSlug(FormBuilderInterface $builder, string $field, string $groupClass = NULL)
    {
        $fieldType = !empty($this->options['fields_type'][$field]) ? $this->options['fields_type'][$field] : Type\TextType::class;
        $builder->add($field, $fieldType, [
            'required' => in_array($field, $this->options['required_fields']),
            'label' => $this->getAttribute($field, 'label'),
            'attr' => [
                'placeholder' => $this->getAttribute($field, 'placeholder'),
                'group' => !empty($this->options['fields'][$field]) ? $this->options['fields'][$field] : 'col-12'
            ],
            'help' => $this->getAttribute($field, 'help')
        ]);
    }

    /**
     * Get constraints
     *
     * @param bool $isRequired
     * @return array
     */
    private function getConstraints(bool $isRequired)
    {
        $constraints = [];

        if ($isRequired) {
            $constraints[] = new Assert\NotBlank();
        }

        return $constraints;
    }

    /**
     * Get alignments
     *
     * @return array
     */
    private function getAlignments()
    {
        return [
            $this->translator->trans("Gauche", [], 'admin') => "text-left",
            $this->translator->trans("Centré", [], 'admin') => "text-center",
            $this->translator->trans("Droit", [], 'admin') => "text-right",
            $this->translator->trans("Justifié", [], 'admin') => "text-justify"
        ];
    }

    /**
     * Get label attribute
     *
     * @param string $field
     * @param string $type
     * @return bool|string|null
     */
    private function getAttribute(string $field, string $type)
    {
        $booleanTypes = ['label'];
        $emptyAttribute = in_array($type, $booleanTypes) ? false : NULL;
        $optionKey = $type . '_fields';
        $attribute = $this->options[$optionKey][$field] ?? $this->getTranslationAttribute($field, $type);

        if (!$attribute) {
            $attribute = $emptyAttribute;
        }

        return $attribute;
    }

    /**
     * Get translation attribute
     *
     * @param string $field
     * @param string $type
     * @return string|null
     */
    private function getTranslationAttribute(string $field, string $type): ?string
    {
        $translations['title'] = [
            'label' => $this->translator->trans('Titre', [], 'admin'),
            'placeholder' => $this->translator->trans('Saisissez un titre', [], 'admin')
        ];
        $translations['subTitle'] = [
            'label' => $this->translator->trans('Sous-titre', [], 'admin'),
            'placeholder' => $this->translator->trans('Saisissez un sous-titre', [], 'admin')
        ];
        $translations['subTitlePosition'] = [
            'label' => $this->translator->trans('Position du sous-titre', [], 'admin'),
            'placeholder' => $this->translator->trans('Séléctionnez', [], 'admin')
        ];
        $translations['titleForce'] = [
            'label' => $this->translator->trans('Force du titre', [], 'admin'),
            'placeholder' => $this->translator->trans('Sélectionnez', [], 'admin')
        ];
        $translations['titleAlignment'] = [
            'label' => $this->translator->trans('Alignement du titre', [], 'admin'),
            'placeholder' => $this->translator->trans('Par défaut', [], 'admin')
        ];
        $translations['introduction'] = [
            'label' => $this->translator->trans('Introduction', [], 'admin'),
            'placeholder' => $this->translator->trans('Saisissez une introduction', [], 'admin')
        ];
        $translations['introductionAlignment'] = [
            'label' => $this->translator->trans("Alignement de l'introduction", [], 'admin'),
            'placeholder' => $this->translator->trans('Par défaut', [], 'admin')
        ];
        $translations['body'] = [
            'label' => $this->translator->trans("Description", [], 'admin'),
            'placeholder' => $this->translator->trans('Saisissez une description', [], 'admin')
        ];
        $translations['headerTable'] = [
            'label' => $this->translator->trans("Afficher les entêtes sur les tableaux", [], 'admin')
        ];
        $translations['bodyAlignment'] = [
            'label' => $this->translator->trans("Alignement de la description", [], 'admin'),
            'placeholder' => $this->translator->trans('Par défaut', [], 'admin')
        ];
        $translations['targetLink'] = [
            'label' => $this->translator->trans("URL de destination", [], 'admin'),
            'placeholder' => $this->translator->trans('Saisissez une URL', [], 'admin')
        ];
        $translations['targetPage'] = [
            'label' => $this->translator->trans("Page de destination", [], 'admin'),
            'placeholder' => $this->translator->trans('Sélectionnez une page', [], 'admin')
        ];
        $translations['targetLabel'] = [
            'label' => $this->translator->trans("Label du lien", [], 'admin'),
            'placeholder' => $this->translator->trans('Saisissez un label', [], 'admin')
        ];
        $translations['targetAlignment'] = [
            'label' => $this->translator->trans("Alignement du lien", [], 'admin'),
            'placeholder' => $this->translator->trans('Par défaut', [], 'admin')
        ];
        $translations['newTab'] = [
            'label' => $this->translator->trans("Ouvrir dans un nouvel onglet", [], 'admin')
        ];
        $translations['placeholder'] = [
            'label' => $this->translator->trans("Intitulé dans le champs", [], 'admin'),
            'placeholder' => $this->translator->trans('Saisissez un intitulé', [], 'admin')
        ];
        $translations['author'] = [
            'label' => $this->translator->trans("Auteur", [], 'admin'),
            'placeholder' => $this->translator->trans('Saisissez un auteur', [], 'admin')
        ];
        $translations['authorType'] = [
            'label' => $this->translator->trans("Type d'auteur", [], 'admin'),
            'placeholder' => $this->translator->trans('Sélectionnez', [], 'admin')
        ];
        $translations['help'] = [
            'label' => $this->translator->trans("Aide", [], 'admin'),
            'placeholder' => $this->translator->trans('Saisissez un message', [], 'admin')
        ];
        $translations['error'] = [
            'label' => $this->translator->trans("Message d'erreur", [], 'admin'),
            'placeholder' => $this->translator->trans('Saisissez un message', [], 'admin')
        ];
        $translations['pictogram'] = [
            'label' => $this->translator->trans("Picto", [], 'admin'),
            'placeholder' => $this->translator->trans('Sélectionnez', [], 'admin')
        ];
        $translations['video'] = [
            'label' => $this->translator->trans("Lien de la vidéo", [], 'admin'),
            'placeholder' => $this->translator->trans('Saisissez une URL', [], 'admin'),
            'help' => $this->translator->trans('Youtube, Vimeo, Dailymotion, Facebook', [], 'admin')
        ];
        $translations['slug'] = [
            'label' => $this->translator->trans("Code", [], 'admin'),
            'placeholder' => $this->translator->trans('Saisissez un code', [], 'admin')
        ];
        $translations['active'] = [
            'label' => $this->translator->trans("Activer", [], 'admin')
        ];

        return !empty($translations[$field][$type]) ? $translations[$field][$type] : NULL;
    }

    /**
     * Get pictograms choices
     *
     * @param Website $website
     * @return array
     */
    private function getPictograms(Website $website)
    {
        /** @var Folder $folder */
        $folder = $this->folderRepository->findOneBy([
            'website' => $website,
            'slug' => 'pictogram'
        ]);

        $markers = [];
        foreach ($folder->getMedias() as $media) {
            $markers[$media->getFilename()] = '/uploads/' . $website->getUploadDirname() . '/' . $media->getFilename();
        }

        return $markers;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => i18n::class,
            'fields' => ['title', 'body'],
            'excludes_fields' => [],
            'required_fields' => [],
            'config_fields' => [],
            'groups_fields' => [],
            'label_fields' => [],
            'placeholder_fields' => [],
            'help_fields' => [],
            'fields_data' => [],
            'fields_type' => [],
            'title_force' => false,
            'content_config' => true,
            'target_config' => true,
            'data_config' => false,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}