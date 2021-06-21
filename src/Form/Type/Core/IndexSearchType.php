<?php

namespace App\Form\Type\Core;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * IndexSearchType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class IndexSearchType extends AbstractType
{
    private $translator;

    /**
     * IndexSearchType constructor.
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
        $builder->add('search', TextType::class, array(
            'required' => false,
            'attr' => ['placeholder' => $this->translator->trans('Rechercher', [], 'admin')],
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'admin',
            'fields' => ['adminName', 'position'],
            'interface' => [],
            'website' => NULL,
            'data_class' => NULL,
            'block_name' => 'search',
            'csrf_protection' => false
        ]);
    }
}