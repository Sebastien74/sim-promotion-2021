<?php

namespace App\Form\Type\Seo;

use App\Entity\Core\Website;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * WebsiteRedirectionType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class WebsiteRedirectionType extends AbstractType
{
    private $translator;

    /**
     * WebsiteRedirectionType constructor.
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
        $builder->add('redirections', CollectionType::class, [
            'label' => false,
            'entry_type' => RedirectionType::class,
            'allow_add' => true,
            'prototype' => true,
            'by_reference' => false,
            'entry_options' => [
                'attr' => ['class' => 'redirection'],
                'website' => $options['website']
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Website::class,
            'website' => NULL,
            'not_found_url' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}