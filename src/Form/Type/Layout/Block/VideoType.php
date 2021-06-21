<?php

namespace App\Form\Type\Layout\Block;

use App\Entity\Layout\Block;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * VideoType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class VideoType extends AbstractType
{
    private $translator;

    /**
     * VideoType constructor.
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
            'fields' => ['targetLink'],
            'target_config' => false,
            'label_fields' => ['targetLink' => $this->translator->trans('Lien de la vidéo', [], 'admin')],
            'help_fields' => ['targetLink' => $this->translator->trans('Youtube, Vimeo, Dailymotion', [], 'admin')]
        ]);

        $mediaRelations = new WidgetType\MediaRelationsCollectionType($this->translator);
        $mediaRelations->add($builder, [
            'entry_options' => ['onlyMedia' => true, 'video' => true]
        ]);

        $builder->add('autoplay', CheckboxType::class, [
            'required' => false,
            'label' => $this->translator->trans('Lecture automatique ?', [], 'admin'),
            'attr' => ['group' => 'col-md-3']
        ]);

        $builder->add('controls', CheckboxType::class, [
            'required' => false,
            'label' => $this->translator->trans('Afficher les boutons de contrôle ?', [], 'admin'),
            'attr' => ['group' => 'col-md-3']
        ]);

        $builder->add('soundControls', CheckboxType::class, [
            'required' => false,
            'label' => $this->translator->trans('Afficher le bouton de contrôle du son ?', [], 'admin'),
            'attr' => ['group' => 'col-md-3']
        ]);

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