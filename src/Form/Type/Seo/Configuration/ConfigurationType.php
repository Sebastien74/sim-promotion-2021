<?php

namespace App\Form\Type\Seo\Configuration;

use App\Entity\Seo\SeoConfiguration;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * ConfigurationType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ConfigurationType extends AbstractType
{
    private $translator;

    /**
     * ConfigurationType constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('website', WebsiteType::class, [
            'label' => false
        ]);

        $builder->add('disabledIps', WidgetType\TagInputType::class, [
            'label' => $this->translator->trans("Désactiver IPS", [], 'admin'),
            'required' => false,
            'attr' => [
                'placeholder' => $this->translator->trans('Ajouter des IPS', [], 'admin')
            ]
        ]);

        $builder->add('microData', Type\CheckboxType::class, [
            'label' => $this->translator->trans('Activer les micros données', [], 'admin'),
            'attr' => ['group' => 'col-12 mb-0'],
            'display' => 'switch'
        ]);

        $i18ns = new WidgetType\i18nsCollectionType($this->translator);
        $i18ns->add($builder, [
            'website' => $options['website'],
            'fields' => ['title' => 'col-md-3', 'placeholder' => 'col-md-3', 'author', 'authorType'],
            'label_fields' => [
                'title' => $this->translator->trans('Méta titre par défault (après le tiret)', [], 'admin'),
                'placeholder' => $this->translator->trans('Type de site (Microdata)', [], 'admin'),
                'author' => $this->translator->trans('Auteur (Microdata)', [], 'admin'),
                'authorType' => $this->translator->trans("Type d'auteur (Microdata)", [], 'admin'),
            ],
            'placeholder_fields' => [
                'placeholder' => $this->translator->trans('Saisissez un type', [], 'admin')
            ],
            'help_fields' => [
                'title' => $this->translator->trans('Valeur par défaut', [], 'admin'),
                'placeholder' => $this->translator->trans('Valeur par défaut', [], 'admin'),
                'author' => $this->translator->trans('Valeur par défaut', [], 'admin'),
                'authorType' => $this->translator->trans("Valeur par défaut", [], 'admin'),
            ],
            'content_config' => false
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SeoConfiguration::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}