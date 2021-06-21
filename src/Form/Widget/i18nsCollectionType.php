<?php

namespace App\Form\Widget;

use App\Form\EventListener\Translation\i18nsListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * i18nsCollectionType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class i18nsCollectionType extends AbstractType
{
    private $translator;

    /**
     * i18nsCollectionType constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Add type
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function add(FormBuilderInterface $builder, array $options = [])
    {
        $disableTitle = !empty($options['disableTitle']) ? $options['disableTitle'] : false;
        if (!empty($options['disableTitle'])) {
            unset($options['disableTitle']);
        }

        $builder->add('i18ns', CollectionType::class, [
            'label' => false,
            'entry_type' => i18nType::class,
            'entry_options' => $options,
            'attr' => [
                'data-config' => !empty($options['data_config']) ? $options['data_config'] : NULL,
                'disableTitle' => $disableTitle
            ]
        ])->addEventSubscriber(new i18nsListener($options));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'admin',
            'website' => NULL,
            'disableTitle' => false,
            'data_config' => false
        ]);
    }
}