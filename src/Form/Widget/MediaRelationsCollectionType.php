<?php

namespace App\Form\Widget;

use App\Form\EventListener\Media\MediaRelationListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * MediaRelationsCollectionType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class MediaRelationsCollectionType extends AbstractType
{
    private $translator;

    /**
     * MediaRelationsCollectionType constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Add fields
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function add(FormBuilderInterface $builder, array $options = [])
    {
        $builder->add('mediaRelations', CollectionType::class, [
            'label' => false,
            'entry_type' => MediaRelationType::class,
            'entry_options' => !empty($options['entry_options']) ? $options['entry_options'] : NULL,
            'attr' => [
                'data-config' => !empty($options['data_config']) ? $options['data_config'] : NULL
            ]
        ])->addEventSubscriber(new MediaRelationListener());
    }
}