<?php

namespace App\Form\Type\Module\Search;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * FrontType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FrontType extends AbstractType
{
    private $translator;

    /**
     * FrontType constructor.
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
        $builder->add('search', TextType::class, [
            'label' => false,
            'data' => $options['field_data'],
            'attr' => [
                'class' => 'border-primary',
                'placeholder' => $this->translator->trans("Saisissez votre recherche", [], 'front_form')
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'field_data' => NULL,
            'csrf_protection' => false,
            'data_class' => NULL,
            'website' => NULL,
            'translation_domain' => 'front_form'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return NULL;
    }
}