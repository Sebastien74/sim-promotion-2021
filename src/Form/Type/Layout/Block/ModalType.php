<?php

namespace App\Form\Type\Layout\Block;

use App\Entity\Layout\Block;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * ModalType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ModalType extends AbstractType
{
    private $translator;

    /**
     * ModalType constructor.
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
            'content_config' => false,
            'excludes_fields' => ['headerTable'],
            'label_fields' => ['placeholder' => $this->translator->trans('Intitulé du bouton', [], 'admin')],
            'fields' => ['title' => 'col-md-4', 'subTitle' => 'col-md-4', 'placeholder' => 'col-md-4', 'introduction', 'body']
        ]);

        $builder->add('timer', Type\IntegerType::class, [
            'required' => false,
            'label' => $this->translator->trans("Temps avant apparition", [], 'admin'),
            'help' => $this->translator->trans("En secondes - S'affichera automatiquement sans le bouton", [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans("Saisissez une durée", [], 'admin'),
                'group' => 'col-md-4',
                'data-config' => true
            ]
        ]);

        $mediaRelations = new WidgetType\MediaRelationsCollectionType($this->translator);
        $mediaRelations->add($builder);

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