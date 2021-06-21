<?php

namespace App\Form\Widget;

use App\Entity\Layout\Page;
use App\Entity\Seo\Url;
use App\Form\Type\Seo\SeoBasicType;
use App\Form\Validator\UniqUrl;
use App\Twig\Content\IconRuntime;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * UrlType
 *
 * @property TranslatorInterface $translator
 * @property IconRuntime $iconRuntime
 * @property array $options
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class UrlType extends AbstractType
{
    private $translator;
    private $iconRuntime;
    private $options = [];

    /**
     * UrlType constructor.
     *
     * @param TranslatorInterface $translator
     * @param IconRuntime $iconRuntime
     */
    public function __construct(TranslatorInterface $translator, IconRuntime $iconRuntime)
    {
        $this->translator = $translator;
        $this->iconRuntime = $iconRuntime;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->options = $options;

        foreach ($options['fields'] as $key => $name) {
            $field = is_int($key) ? $name : $key;
            $groupClass = is_int($key) ? 'col-12' : $name;
            $getter = 'get' . ucfirst($field);
            if (method_exists($this, $getter)) {
                $this->$getter($builder, $field, $groupClass);
            }
        }

        if ($options['display_seo']) {
            $builder->add('seo', SeoBasicType::class);
        }
    }

    /**
     * Code field
     *
     * @param FormBuilderInterface $builder
     * @param string $field
     * @param string|null $groupClass
     */
    private function getCode(FormBuilderInterface $builder, string $field, string $groupClass = NULL)
    {
        $builder->add($field, Type\TextType::class, [
            'required' => in_array($field, $this->options['required_fields']),
            'label' => $this->getAttribute($field, 'label'),
            'attr' => [
                'placeholder' => $this->getAttribute($field, 'placeholder'),
                'code' => 'code',
                'group' => !empty($this->options['fields'][$field]) ? $this->options['fields'][$field] : 'col-12'
            ],
            'constraints' => [new UniqUrl()],
            'help' => $this->getAttribute($field, 'help')
        ]);
    }

    /**
     * Code field
     *
     * @param FormBuilderInterface $builder
     * @param string $field
     * @param string|null $groupClass
     */
    private function getHideInSitemap(FormBuilderInterface $builder, string $field, string $groupClass = NULL)
    {
        $builder->add($field, Type\CheckboxType::class, [
            'required' => in_array($field, $this->options['required_fields']),
            'display' => 'button',
            'color' => 'outline-dark',
            'label' =>  $this->getAttribute($field, 'label'),
            'attr' => ['group' => !empty($this->options['fields'][$field]) ? $this->options['fields'][$field] : 'col-12', 'class' => 'w-100'],
            'help' => $this->getAttribute($field, 'help')
        ]);
    }

    /**
     * Code field
     *
     * @param FormBuilderInterface $builder
     * @param string $field
     * @param string|null $groupClass
     */
    private function getIsIndex(FormBuilderInterface $builder, string $field, string $groupClass = NULL)
    {
        $builder->add($field, Type\CheckboxType::class, [
            'required' => in_array($field, $this->options['required_fields']),
            'display' => 'button',
            'color' => 'outline-dark',
            'label' =>  $this->getAttribute($field, 'label'),
            'attr' => ['group' => !empty($this->options['fields'][$field]) ? $this->options['fields'][$field] : 'col-12', 'class' => 'w-100'],
            'help' => $this->getAttribute($field, 'help')
        ]);
    }

    /**
     * Code field
     *
     * @param FormBuilderInterface $builder
     * @param string $field
     * @param string|null $groupClass
     */
    private function getIsOnline(FormBuilderInterface $builder, string $field, string $groupClass = NULL)
    {
        $builder->add($field, Type\ChoiceType::class, [
            'label' => false,
            'choices' => [
                $this->translator->trans('En ligne', [], 'admin') => true,
                $this->translator->trans('Hors ligne', [], 'admin') => false
            ],
            'attr' => ['class' => 'select-icons'],
            'choice_attr' => function ($boolean, $key, $value) {
                if ($boolean === true) {
                    return [
                        'data-svg' => $this->iconRuntime->fontawesome('far fa-check', 17, 17, 'mr-2 success', NULL, false),
                        'data-text' => true,
                    ];
                } else {
                    return [
                        'data-svg' => $this->iconRuntime->fontawesome('far fa-ban', 17, 17, 'mr-2 danger', NULL, false),
                        'data-text' => true,
                    ];
                }
            },
        ]);
    }

    /**
     * Code index Page field
     *
     * @param FormBuilderInterface $builder
     * @param string $field
     * @param string|null $groupClass
     */
    private function getIndexPage(FormBuilderInterface $builder, string $field, string $groupClass = NULL)
    {
        $builder->add($field, EntityType::class, [
            'required' => false,
            'display' => 'search',
            'label' => $this->translator->trans("Page de l'index", [], 'admin'),
            'placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
            'class' => Page::class,
            'choice_label' => function ($entity) {
                return strip_tags($entity->getAdminName());
            }
        ]);
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
        $booleanTypes = ['hideInSitemap', 'isOnline', 'isIndex'];
        $emptyAttribute = in_array($type, $booleanTypes) ? false : NULL;
        $optionKey = $type . '_fields';

        $attribute = isset($this->options[$optionKey][$field])
            ? $this->options[$optionKey][$field]
            : $this->getTranslationAttribute($field, $type);

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
        $translations['code'] = [
            'label' => $this->translator->trans('URL', [], 'admin'),
            'placeholder' => $this->translator->trans('Saisissez un titre', [], 'admin')
        ];
        $translations['hideInSitemap'] = [
            'label' => $this->translator->trans('Cacher dans plan de site', [], 'admin')
        ];
        $translations['isIndex'] = [
            'label' => $this->translator->trans('Robots index', [], 'admin')
        ];

        return !empty($translations[$field][$type]) ? $translations[$field][$type] : NULL;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Url::class,
            'fields' => ['code', 'hideInSitemap', 'isOnline'],
            'required_fields' => [],
            'label_fields' => [],
            'help_fields' => [],
            'display_seo' => false,
            'translation_domain' => 'admin'
        ]);
    }
}