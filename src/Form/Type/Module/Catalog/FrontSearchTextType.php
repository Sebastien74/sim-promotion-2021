<?php

namespace App\Form\Type\Module\Catalog;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * FrontSearchTextType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FrontSearchTextType extends AbstractType
{
    private $translator;

    /**
     * SearchTextType constructor.
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
        $builder->add('text', Type\TextType::class, array(
            'label' => false,
            'required' => false,
            'data' => $options['text'],
            'property_path' => 'text',
            'attr' => [
                'placeholder' => $this->translator->trans("Rechercher ...", [], 'front_form')
            ]
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => NULL,
            'text' => NULL,
            'translation_domain' => 'front_form',
            'csrf_protection' => false
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return "search_products";
    }
}