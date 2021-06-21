<?php

namespace App\Form\Type\Seo\Configuration;

use App\Entity\Information\Information;
use App\Form\Type\Information\SocialNetworkType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * InformationType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class InformationType extends AbstractType
{
    private $translator;

    /**
     * InformationType constructor.
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
        $builder->add('socialNetworks', CollectionType::class, [
            'label' => false,
            'entry_type' => SocialNetworkType::class
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Information::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}