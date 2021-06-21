<?php

namespace App\Form\Type\Information;

use App\Entity\Information\SocialNetwork;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * SocialNetworkType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SocialNetworkType extends AbstractType
{
    private $translator;

    /**
     * SocialNetworkType constructor.
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
        $builder->add('facebook', Type\TextType::class, [
            'required' => false,
            'label' => false,
            'attr' => [
                'placeholder' => $this->translator->trans('Saisissez une URL', [], 'admin'),
                'addon' => "fab fa-facebook-f"
            ]
        ]);

        $builder->add('twitter', Type\TextType::class, [
            'required' => false,
            'label' => false,
            'attr' => [
                'placeholder' => $this->translator->trans('Saisissez une URL', [], 'admin'),
                'addon' => "fab fa-twitter"
            ]
        ]);

        $builder->add('google', Type\TextType::class, [
            'required' => false,
            'label' => false,
            'attr' => [
                'placeholder' => $this->translator->trans('Saisissez une URL', [], 'admin'),
                'addon' => "fab fa-google"
            ]
        ]);

        $builder->add('youtube', Type\TextType::class, [
            'required' => false,
            'label' => false,
            'attr' => [
                'placeholder' => $this->translator->trans('Saisissez une URL', [], 'admin'),
                'addon' => "fab fa-youtube"
            ]
        ]);

        $builder->add('instagram', Type\TextType::class, [
            'required' => false,
            'label' => false,
            'attr' => [
                'placeholder' => $this->translator->trans('Saisissez une URL', [], 'admin'),
                'addon' => "fab fa-instagram"
            ]
        ]);

        $builder->add('linkedin', Type\TextType::class, [
            'required' => false,
            'label' => false,
            'attr' => [
                'placeholder' => $this->translator->trans('Saisissez une URL', [], 'admin'),
                'addon' => "fab fa-linkedin-in"
            ]
        ]);

        $builder->add('pinterest', Type\TextType::class, [
            'required' => false,
            'label' => false,
            'attr' => [
                'placeholder' => $this->translator->trans('Saisissez une URL', [], 'admin'),
                'addon' => "fab fa-pinterest-p"
            ]
        ]);

        $builder->add('tripadvisor', Type\TextType::class, [
            'required' => false,
            'label' => false,
            'attr' => [
                'placeholder' => $this->translator->trans('Saisissez une URL', [], 'admin'),
                'addon' => "fab fa-tripadvisor"
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SocialNetwork::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}