<?php

namespace App\Form\Type\Seo\Configuration;

use App\Entity\Api\Api;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * ApiType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ApiType extends AbstractType
{
    private $translator;

    /**
     * ApiType constructor.
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
        $builder->add('google', GoogleType::class, [
            'label' => false
        ]);

        $builder->add('facebook', FacebookType::class, [
            'label' => false
        ]);

        $builder->add('instagram', InstagramType::class, [
            'label' => false
        ]);

        $builder->add('custom', CustomType::class, [
            'label' => false
        ]);

        $builder->add('shareLinks', Type\ChoiceType::class, [
            'label' => $this->translator->trans('Liens de partage', [], 'admin'),
            'required' => false,
            'expanded' => false,
            'display' => 'search',
            'multiple' => true,
            'placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
            'attr' => [
                'data-placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
                'placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
            ],
            'choices' => [
                'Facebook' => 'facebook',
                'Twitter' => 'twitter',
                'Pinterest' => 'pinterest',
                'Linkedin' => 'linkedin',
                'E-mail' => 'email'
            ]
        ]);

        $builder->add('addThis', Type\TextareaType::class, [
            'required' => false,
            'editor' => false,
            'label' => $this->translator->trans('AddThis script', [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans('Ajouter le script', [], 'admin')
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Api::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}