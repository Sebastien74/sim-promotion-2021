<?php

namespace App\Form\Type\Seo;

use App\Entity\Seo\Model;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * ModelType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ModelType extends AbstractType
{
    private $translator;

    /**
     * ModelType constructor.
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
        $builder->add('metaTitle', Type\TextType::class, [
            'label' => $this->translator->trans('Méta titre', [], 'admin'),
            'counter' => 72,
            'attr' => [
                'placeholder' => $this->translator->trans("Saisissez un titre", [], 'admin'),
                'class' => "meta-title"
            ],
            'required' => false
        ]);

        $builder->add('metaTitleSecond', Type\TextType::class, [
            'label' => $this->translator->trans('Méta titre (après le tiret)', [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans("Saisissez un titre", [], 'admin'),
                'class' => "meta-title-second"
            ],
            'required' => false
        ]);

        $builder->add('metaDescription', Type\TextareaType::class, [
            'label' => $this->translator->trans('Méta description', [], 'admin'),
            'counter' => 155,
            'editor' => false,
            'attr' => [
                'placeholder' => $this->translator->trans("Éditez une description", [], 'admin'),
                'class' => "meta-description"
            ],
            'required' => false
        ]);

        $builder->add('footerDescription', Type\TextareaType::class, [
            'label' => $this->translator->trans('Description pied de page', [], 'admin'),
            'editor' => false,
            'attr' => [
                'placeholder' => $this->translator->trans("Éditez une description", [], 'admin'),
                'class' => "footer-description"
            ],
            'required' => false
        ]);

        $builder->add('noAfterDash', Type\CheckboxType::class, [
            'required' => false,
            'display' => 'button',
            'color' => 'outline-dark',
            'label' =>  $this->translator->trans('Désactiver après tiret', [], 'admin'),
            'attr' => ['group' => 'col-12', 'class' => 'w-100'],
        ]);

        $builder->add('metaOgTitle', Type\TextType::class, [
            'label' => $this->translator->trans('OG Méta titre', [], 'admin'),
            'counter' => 72,
            'attr' => [
                'placeholder' => $this->translator->trans("Saisissez un titre", [], 'admin'),
                'class' => "meta-og-title"
            ],
            'required' => false
        ]);

        $builder->add('metaOgDescription', Type\TextareaType::class, [
            'label' => $this->translator->trans('OG Méta description', [], 'admin'),
            'counter' => 155,
            'editor' => false,
            'attr' => [
                'placeholder' => $this->translator->trans("Éditez une description", [], 'admin'),
                'class' => "meta-og-description"
            ],
            'required' => false
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Model::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}