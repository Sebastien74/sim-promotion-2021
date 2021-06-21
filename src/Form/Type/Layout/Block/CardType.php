<?php

namespace App\Form\Type\Layout\Block;

use App\Entity\Layout\Block;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CardType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CardType extends AbstractType
{
    private $translator;

    /**
     * CardType constructor.
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
        $builder->add('template', WidgetType\TemplateBlockType::class);

        $i18ns = new WidgetType\i18nsCollectionType($this->translator);
        $i18ns->add($builder, [
            'website' => $options['website'],
            'fields' => ['title' => 'col-md-5', 'subTitle' => 'col-md-5', 'body', 'targetLink' => 'col-md-8 add-title', 'targetPage' => 'col-md-4', 'newTab' => 'col-md-3'],
            'excludes_fields' => ['targetStyle', 'headerTable', 'targetAlignment'],
            'content_config' => false
        ]);

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
            'data_class' => Block::class,
            'translation_domain' => 'admin',
            'website' => NULL
        ]);
    }
}