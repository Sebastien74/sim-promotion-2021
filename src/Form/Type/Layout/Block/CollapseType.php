<?php

namespace App\Form\Type\Layout\Block;

use App\Entity\Layout\Block;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CollapseType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CollapseType extends AbstractType
{
    private $translator;

    /**
     * TextType constructor.
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
            'fields' => [
                'title' => 'col-md-6', 'placeholder' => 'col-md-4', 'introduction', 'body'
            ],
            'label_fields' => [
                'title' => $this->translator->trans('Titre', [], 'admin'),
                'placeholder' => $this->translator->trans('Intitulé du bouton', [], 'admin')
            ],
            'excludes_fields' => ['headerTable'],
            'placeholder_fields' => ['title' => $this->translator->trans('Saisissez un intitulé', [], 'admin')]
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