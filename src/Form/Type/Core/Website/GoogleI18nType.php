<?php

namespace App\Form\Type\Core\Website;

use App\Entity\Api\GoogleI18n;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * GoogleI18nType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class GoogleI18nType extends AbstractType
{
    private $translator;

    /**
     * GoogleI18nType constructor.
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
        $builder->add('analyticsUa', Type\TextType::class, [
            'required' => false,
            'label' => $this->translator->trans('User agent', [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans('Saisissez le UA', [], 'admin'),
                'group' => "col-md-4"
            ]
        ]);

        $builder->add('tagManagerKey', Type\TextType::class, [
            'required' => false,
            'label' => $this->translator->trans('Tag manager key', [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans('Saisissez la clé', [], 'admin'),
                'group' => "col-md-4"
            ]
        ]);

        $builder->add('searchConsoleKey', Type\TextType::class, [
            'required' => false,
            'label' => $this->translator->trans('Search console key', [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans('Saisissez la clé', [], 'admin'),
                'group' => "col-md-4"
            ]
        ]);

        $builder->add('clientId', Type\TextType::class, [
            'required' => false,
            'label' => $this->translator->trans('Client id', [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans('Saisissez un id', [], 'admin'),
                'group' => "col-md-8"
            ],
            'help' => $this->translator->trans('Create ID client AUTH identification in API account. Ex: xxxxxxxxxxxx-xxxxxxxxxx.apps.googleusercontent.com', [], 'admin')
        ]);

        $builder->add('analyticsAccountId', Type\TextType::class, [
            'required' => false,
            'label' => $this->translator->trans('Analytics account id', [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans('Saisissez un id', [], 'admin'),
                'group' => "col-md-4"
            ],
            'help' => $this->translator->trans('Create ID client AUTH identification in API account and activate Analytics API. ga:xxxxxxx', [], 'admin')
        ]);

        $builder->add('tagManagerLayer', Type\TextareaType::class, [
            'required' => false,
            'editor' => false,
            'label' => $this->translator->trans('Data layer script', [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans('Éditez le dataLayer', [], 'admin'),
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => GoogleI18n::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}