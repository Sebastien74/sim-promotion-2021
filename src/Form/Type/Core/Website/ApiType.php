<?php

namespace App\Form\Type\Core\Website;

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
        $builder->add('securitySecretKey', Type\TextType::class, [
            'required' => false,
            'bytes' => true,
            'label' => $this->translator->trans('Clé privée', [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans('Saisissez la clé', [], 'admin'),
                'group' => 'col-md-6'
            ]
        ]);

        $builder->add('securitySecretIv', Type\TextType::class, [
            'required' => false,
            'bytes' => true,
            'label' => $this->translator->trans('Clé de décryptage', [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans('Saisissez la clé', [], 'admin'),
                'group' => 'col-md-6'
            ]
        ]);

        $builder->add('google', GoogleType::class, [
            'label' => false
        ]);

        $builder->add('instagram', InstagramType::class, [
            'label' => false
        ]);

        $builder->add('addThis', Type\TextareaType::class, [
            'required' => false,
            'editor' => false,
            'label' => $this->translator->trans('AddThis script', [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans('Ajouter le script', [], 'admin'),
                'group' => 'col-md-6'
            ]
        ]);

        $builder->add('custom', CustomType::class, [
            'label' => false
        ]);

        $builder->add('tawkToId', Type\TextType::class, [
            'required' => false,
            'label' => $this->translator->trans('TawkTo ID', [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans("Saisissez un ID", [], 'admin'),
                'group' => 'col-md-4'
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