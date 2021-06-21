<?php

namespace App\Form\Type\Layout\Block;

use App\Entity\Core\Entity;
use App\Entity\Layout\Block;
use App\Entity\Layout\FieldConfiguration;
use App\Form\EventListener\Layout\ValuesListener;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * FieldConfigurationType
 *
 * @property TranslatorInterface $translator
 * @property EntityManagerInterface $entityManager
 * @property bool $isInternalUser
 * @property array $options
 * @property FieldConfiguration $data
 * @property bool $isNew
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FieldConfigurationType extends AbstractType
{
    private $translator;
    private $entityManager;
    private $isInternalUser;
    private $options = [];
    private $data;
    private $isNew;

    /**
     * FieldConfigurationType constructor.
     *
     * @param TranslatorInterface $translator
     * @param EntityManagerInterface $entityManager
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(TranslatorInterface $translator, EntityManagerInterface $entityManager, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->isInternalUser = $authorizationChecker->isGranted('ROLE_INTERNAL');
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fieldType = $options['field_type'];
        $fields = $this->getFields($fieldType);
        $this->options = $options;
        $this->data = $this->options['block'] instanceof Block ? $this->options['block']->getFieldConfiguration() : NULL;
        $this->isNew = $this->data && !$this->data->getId();

        if ($this->isInternalUser) {
            $builder->add('slug', Type\TextType::class, [
                'required' => false,
                'label' => $this->translator->trans('Code', [], 'admin'),
                'attr' => [
                    'group' => 'col-md-2',
                    'placeholder' => $this->translator->trans('Saisissez un code', [], 'admin')
                ]
            ]);
        }

        foreach ($fields as $field => $value) {
            $getter = is_bool($value) || preg_match('/col/', $value) ? 'get' . ucfirst($field) : 'get' . ucfirst($value);
            $groupClass = preg_match('/col/', $value) ? $value : NULL;
            if (method_exists($this, $getter) && !is_bool($value)
                || method_exists($this, $getter) && is_bool($value) && $this->isInternalUser) {
                $this->$getter($builder, $fieldType, $groupClass);
            }
        }

        if ($this->isRequired($fieldType, $fields)) {
            $this->getRequired($builder, 'required', NULL);
        }
    }

    /**
     * Get required
     *
     * @param FormBuilderInterface $builder
     * @param string $fieldType
     * @param string|null $groupClass
     */
    private function getRequired(FormBuilderInterface $builder, string $fieldType, string $groupClass = NULL)
    {
        $builder->add('required', Type\CheckboxType::class, [
            'required' => false,
            'label' => $this->translator->trans('Champs obligatoire ?', [], 'admin'),
            'attr' => ['group' => $groupClass ? $groupClass : 'col-md-3']
        ]);
    }

    /**
     * Get Min IntegerType
     *
     * @param FormBuilderInterface $builder
     * @param string $fieldType
     * @param string|null $groupClass
     */
    private function getMin(FormBuilderInterface $builder, string $fieldType, string $groupClass = NULL)
    {
        $label = $this->getLabel($fieldType, 'min');
        $label = !empty($label) ? $label : $this->translator->trans('Nombre minimun de caractères', [], 'admin');
        $placeholder = $this->getPlaceholder($fieldType, 'min');
        $placeholder = !empty($placeholder) ? $placeholder : $this->translator->trans('Saisissez un chiffre', [], 'admin');

        $builder->add('min', Type\IntegerType::class, [
            'required' => false,
            'label' => $label,
            'attr' => [
                'placeholder' => $placeholder,
                'group' => $groupClass ? $groupClass : 'col-md-4',
                'min' => 0
            ]
        ]);
    }

    /**
     * Get Max IntegerType
     *
     * @param FormBuilderInterface $builder
     * @param string $fieldType
     * @param string|null $groupClass
     */
    private function getMax(FormBuilderInterface $builder, string $fieldType, string $groupClass = NULL)
    {
        $label = $this->getLabel($fieldType, 'max');
        $label = !empty($label) ? $label : $this->translator->trans('Nombre maximum de caractères', [], 'admin');
        $placeholder = $this->getPlaceholder($fieldType, 'max');
        $placeholder = !empty($placeholder) ? $placeholder : $this->translator->trans('Saisissez un chiffre', [], 'admin');

        $builder->add('max', Type\IntegerType::class, [
            'required' => false,
            'label' => $label,
            'attr' => [
                'placeholder' => $placeholder,
                'group' => $groupClass ? $groupClass : 'col-md-4',
                'min' => 0
            ]
        ]);
    }

    /**
     * Get Max IntegerType
     *
     * @param FormBuilderInterface $builder
     * @param string $fieldType
     * @param string|null $groupClass
     */
    private function getRegex(FormBuilderInterface $builder, string $fieldType, string $groupClass = NULL)
    {
        $builder->add('regex', Type\TextType::class, [
            'required' => false,
            'label' => $this->translator->trans('Expression regulière', [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans('Éditez une expression', [], 'admin'),
                'group' => $groupClass ? $groupClass : 'col-md-4'
            ],
            'help' => $this->translator->trans('Ex: /^[0-9]*$/', [], 'admin')
        ]);
    }

    /**
     * Get Multiple CheckboxType
     *
     * @param FormBuilderInterface $builder
     * @param string $fieldType
     * @param string|null $groupClass
     */
    private function getMultiple(FormBuilderInterface $builder, string $fieldType, string $groupClass = NULL)
    {
        $builder->add('multiple', Type\CheckboxType::class, [
            'required' => false,
            'label' => $this->translator->trans('Choix multiple ?', [], 'admin'),
            'attr' => [
                'group' => $groupClass ? $groupClass : 'col-md-3'
            ]
        ]);
    }

    /**
     * Get display Checkbox
     *
     * @param FormBuilderInterface $builder
     * @param string $fieldType
     * @param string|null $groupClass
     */
    private function getExpanded(FormBuilderInterface $builder, string $fieldType, string $groupClass = NULL)
    {
        $builder->add('expanded', Type\CheckboxType::class, [
            'required' => false,
            'label' => $this->translator->trans('Afficher les cases à cocher ?', [], 'admin'),
            'attr' => [
                'group' => $groupClass ? $groupClass : 'col-md-3'
            ]
        ]);
    }

    /**
     * Get display Picker
     *
     * @param FormBuilderInterface $builder
     * @param string $fieldType
     * @param string|null $groupClass
     */
    private function getPicker(FormBuilderInterface $builder, string $fieldType, string $groupClass = NULL)
    {
        $label = $this->getLabel($fieldType, 'picker');
        $label = !empty($label) ? $label : $this->translator->trans('Afficher un picker ?', [], 'admin');

        $builder->add('picker', Type\CheckboxType::class, [
            'required' => false,
            'label' => $label,
            'data' => $this->isNew ? true : $this->data->getPicker(),
            'attr' => ['group' => $groupClass ? $groupClass : 'col-md-3']
        ]);
    }

    /**
     * Get display inline
     *
     * @param FormBuilderInterface $builder
     * @param string $fieldType
     * @param string|null $groupClass
     */
    private function getInline(FormBuilderInterface $builder, string $fieldType, string $groupClass = NULL)
    {
        $label = $this->getLabel($fieldType, 'inline');
        $label = !empty($label) ? $label : $this->translator->trans('Afficher en ligne ?', [], 'admin');

        $builder->add('inline', Type\CheckboxType::class, [
            'required' => false,
            'label' => $label,
            'data' => $this->isNew ? false : $this->data->getInline(),
            'attr' => ['group' => $groupClass ? $groupClass : 'col-md-3']
        ]);
    }

    /**
     * Get display Picker
     *
     * @param FormBuilderInterface $builder
     * @param string $fieldType
     * @param string|null $groupClass
     */
    private function getFilesTypes(FormBuilderInterface $builder, string $fieldType, string $groupClass = NULL)
    {
        $builder->add('filesTypes', Type\ChoiceType::class, [
            'required' => false,
            'multiple' => true,
            'display' => 'search',
            'label' => $this->translator->trans('Types de fichiers', [], 'admin'),
            'attr' => ['group' => $groupClass ? $groupClass : 'col-12'],
            'choices' => $this->getMimeTypes()
        ]);
    }

    /**
     * Get display Picker
     *
     * @param FormBuilderInterface $builder
     * @param string $fieldType
     * @param string|null $groupClass
     */
    private function getMaxFileSize(FormBuilderInterface $builder, string $fieldType, string $groupClass = NULL)
    {
        $builder->add('maxFileSize', Type\IntegerType::class, [
            'required' => false,
            'label' => $this->translator->trans('Poid maximum en kilobyte', [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans('Saisissez un chiffre', [], 'admin'),
                'group' => $groupClass ? $groupClass : 'col-md-4',
                'min' => 1
            ]
        ]);
    }

    /**
     * Get script
     *
     * @param FormBuilderInterface $builder
     * @param string $fieldType
     * @param string|null $groupClass
     */
    private function getScript(FormBuilderInterface $builder, string $fieldType, string $groupClass = NULL)
    {
        $builder->add('script', Type\TextareaType::class, [
            'required' => false,
            'editor' => false,
            'label' => $this->translator->trans('Script', [], 'admin'),
            'attr' => [
                'group' => $groupClass ? $groupClass : 'col-12',
                'placeholder' => $this->translator->trans('Ajouter un script', [], 'admin')
            ]
        ]);
    }

    /**
     * Get entity selector
     *
     * @param FormBuilderInterface $builder
     * @param string $fieldType
     * @param string|null $groupClass
     */
    private function getEntity(FormBuilderInterface $builder, string $fieldType, string $groupClass = NULL)
    {
        $choices = [];
        $entities = $this->entityManager->getRepository(Entity::class)->findBy([
            'inFieldConfiguration' => true,
            'website' => $this->options['website']
        ]);
        foreach ($entities as $entity) {
            $choices[$entity->getClassName()] = $entity->getClassName();
        }

        if(!$entities && $this->isInternalUser) {
            $session = new Session();
            $session->getFlashBag()->add('error', $this->translator->trans("Rendez-vous dans la configuration des entités."));
        }

        $builder->add('className', Type\ChoiceType::class, [
            'required' => false,
            'display' => 'search',
            'label' => $this->translator->trans('Entité', [], 'admin'),
            'choices' => $choices,
            'attr' => [
                'group' => $groupClass ? $groupClass : 'col-md-3',
                'data-placeholder' => $this->translator->trans('Sélectionnez', [], 'admin')
            ],
            'constraints' => [new Assert\NotBlank()]
        ]);
    }

    /**
     * Get display Checkbox
     *
     * @param FormBuilderInterface $builder
     * @param string $fieldType
     */
    private function getValues(FormBuilderInterface $builder, string $fieldType)
    {
        $options['website'] = $this->options['website'];
        $options['field_type'] = $this->options['field_type'];

        $builder->add('fieldValues', CollectionType::class, [
            'label' => false,
            'entry_type' => FieldValueType::class,
            'prototype' => true,
            'allow_add' => true,
            'allow_delete' => true,
            'entry_options' => $options,
        ])->addEventSubscriber(new ValuesListener($this->options));
    }

    /**
     * Get fields for field type
     *
     * @param string $fieldType
     * @return array
     */
    private function getFields(string $fieldType): array
    {
        $fields['form-text'] = ['min', 'max', 'regex' => true];
        $fields['form-textarea'] = ['min', 'max', 'regex' => true];
        $fields['form-password'] = ['min', 'max', 'regex' => true];
        $fields['form-integer'] = ['min' => 'col-md-6', 'max' => 'col-md-6'];
        $fields['form-gdpr'] = ['values'];
        $fields['form-choice-type'] = ['multiple', 'expanded', 'values', 'picker', 'inline'];
        $fields['form-emails'] = ['values'];
        $fields['form-date'] = ['picker' => 'col-md-3 my-md-auto', 'min' => 'col-md-2', 'max' => 'col-md-2', 'required' => 'col-md-3 my-md-auto'];
        $fields['form-datetime'] = ['picker' => 'col-md-3 my-md-auto', 'min' => 'col-md-2', 'max' => 'col-md-2', 'required' => 'col-md-3 my-md-auto'];
        $fields['form-hour'] = ['picker'];
        $fields['form-file'] = ['filesTypes' => 'col-md-8', 'maxFileSize' => 'col-md-4', 'multiple'];
        $fields['form-emails'] = ['multiple', 'expanded', 'values', 'picker'];
        $fields['form-country'] = ['picker'];
        $fields['form-language'] = ['picker'];
        $fields['form-choice-entity'] = ['entity', 'picker'];
        $fields['form-submit'] = ['script'];

        return isset($fields[$fieldType]) ? $fields[$fieldType] : [];
    }

    /**
     * Get fields label
     *
     * @param string $fieldType
     * @param string $field
     * @return array
     */
    private function getLabel(string $fieldType, string $field)
    {
        $fields['form-choice-type'] = ['picker' => $this->translator->trans('Afficher le moteur de recherche ?', [], 'admin')];
        $fields['form-emails'] = ['picker' => $this->translator->trans('Afficher le moteur de recherche ?', [], 'admin')];
        $fields['form-country'] = ['picker' => $this->translator->trans('Afficher le moteur de recherche ?', [], 'admin')];
        $fields['form-language'] = ['picker' => $this->translator->trans('Afficher le moteur de recherche ?', [], 'admin')];
        $fields['form-integer'] = [
            'min' => $this->translator->trans('Minimum', [], 'admin'),
            'max' => $this->translator->trans('Maximum', [], 'admin'),
            'picker' => $this->translator->trans('Afficher les selecteurs ?', [], 'admin')
        ];
        $fields['form-date'] = [
            'min' => $this->translator->trans('Année de début', [], 'admin'),
            'max' => $this->translator->trans('Année de fin', [], 'admin')
        ];

        return isset($fields[$fieldType][$field]) ? $fields[$fieldType][$field] : NULL;
    }

    /**
     * Get fields placeholder
     *
     * @param string $fieldType
     * @param string $field
     * @return array
     */
    private function getPlaceholder(string $fieldType, string $field)
    {
        $fields['form-date'] = $fields['form-datetime'] = [
            'min' => $this->translator->trans('Saisissez une année', [], 'admin'),
            'max' => $this->translator->trans('Saisissez une année', [], 'admin')
        ];

        return isset($fields[$fieldType][$field]) ? $fields[$fieldType][$field] : NULL;
    }

    /**
     * Get fields for field type
     *
     * @param string $fieldType
     * @return bool
     */
    private function isRequired(string $fieldType, array $fieldsConfig = []): bool
    {
        foreach ($fieldsConfig as $key => $value) {
            if($key === 'required' || $value === 'required') {
                return false;
            }
        }

        $fields['form-submit'] = false;
        $fields['form-hidden'] = false;

        return isset($fields[$fieldType]) ? $fields[$fieldType] : true;
    }

    /**
     * Get mimeTypes
     *
     * @return array
     */
    private function getMimeTypes(): array
    {
        $mimeTypes = ['.xlsx', '.xls', 'image/*', '.doc', '.docx', '.txt', '.pdf', '.mp4', '.mp3'];

        $choices = [];
        foreach ($mimeTypes as $mimeType) {
            $choices[$mimeType] = $mimeType;
        }

        return $choices;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FieldConfiguration::class,
            'website' => NULL,
            'block' => NULL,
            'field_type' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}