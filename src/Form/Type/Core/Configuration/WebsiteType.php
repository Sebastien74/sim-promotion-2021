<?php

namespace App\Form\Type\Core\Configuration;

use App\Entity\Core\Website;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * WebsiteType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class WebsiteType extends AbstractType
{
    private $translator;

    /**
     * WebsiteType constructor.
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
        $builder->add('information', InformationType::class, [
            'label' => false
        ]);

        $builder->add('api', ApiType::class, [
            'label' => false,
            'website' => $options['website']
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
            'translation_domain' => 'admin'
        ]);
    }
}