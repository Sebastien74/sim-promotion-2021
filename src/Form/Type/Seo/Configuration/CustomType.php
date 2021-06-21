<?php

namespace App\Form\Type\Seo\Configuration;

use App\Entity\Api\Custom;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CustomType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CustomType extends AbstractType
{
    private $translator;

    /**
     * CustomType constructor.
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
        $builder->add('headScriptSeo', Type\TextareaType::class, [
            'required' => false,
            'editor' => false,
            'label' => $this->translator->trans('Script (head)', [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans('Ajouter le script', [], 'admin'),
                'group' => "col-md-4"
            ]
        ]);

        $builder->add('topBodyScriptSeo', Type\TextareaType::class, [
            'required' => false,
            'editor' => false,
            'label' => $this->translator->trans('Script (Top body)', [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans('Ajouter le script', [], 'admin'),
                'group' => "col-md-4"
            ]
        ]);

        $builder->add('bottomBodyScriptSeo', Type\TextareaType::class, [
            'required' => false,
            'editor' => false,
            'label' => $this->translator->trans('Script (Bottom body)', [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans('Ajouter le script', [], 'admin'),
                'group' => "col-md-4"
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Custom::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}