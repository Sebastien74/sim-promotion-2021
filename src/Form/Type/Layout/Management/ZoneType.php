<?php

namespace App\Form\Type\Layout\Management;

use App\Entity\Layout\Zone;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * ZoneType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ZoneType extends AbstractType
{
    private $translator;

    /**
     * ZoneType constructor.
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
        $mediaRelations = new WidgetType\MediaRelationsCollectionType($this->translator);
        $mediaRelations->add($builder, ['entry_options' => ['onlyMedia' => true]]);

        $save = new WidgetType\SubmitType($this->translator);
        $save->add($builder, ['btn_back' => true]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Zone::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}