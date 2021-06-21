<?php

namespace App\Form\Type\Core\Configuration;

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

        $builder->add('displayShareLinks', Type\CheckboxType::class, [
            'required' => false,
            'display' => 'button',
            'color' => 'outline-dark',
            'label' => $this->translator->trans('Afficher les boutons de partage sur toutes les pages', [], 'admin'),
            'attr' => ['group' => 'col-md-4', 'class' => 'w-100']
        ]);

        $builder->add('shareLinkFixed', Type\CheckboxType::class, [
            'required' => false,
            'display' => 'button',
            'color' => 'outline-dark',
            'label' => $this->translator->trans('Bloc de boutons de partage fixé', [], 'admin'),
            'attr' => ['group' => 'col-md-3', 'class' => 'w-100']
        ]);

        $builder->add('displayShareNames', Type\CheckboxType::class, [
            'required' => false,
            'display' => 'button',
            'color' => 'outline-dark',
            'label' => $this->translator->trans('Afficher le nom des réseaux sociaux dans les boutons de partage', [], 'admin'),
            'attr' => ['group' => 'col-md-5', 'class' => 'w-100']
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