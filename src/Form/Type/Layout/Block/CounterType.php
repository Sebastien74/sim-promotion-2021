<?php

namespace App\Form\Type\Layout\Block;

use App\Entity\Layout\Block;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CounterType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CounterType extends AbstractType
{
    private $translator;

    /**
     * CounterType constructor.
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
            'fields' => ['title' => 'col-md-4', 'placeholder' => 'col-md-4', 'slug' => 'col-md-4', 'body'],
            'label_fields' => [
                'title' => $this->translator->trans('Valeur', [], 'admin'),
                'placeholder' => $this->translator->trans('Préfixe', [], 'admin'),
                'slug' => $this->translator->trans('Suffixe', [], 'admin')
            ],
            'placeholder_fields' => [
                'title' => $this->translator->trans('Saisissez une valeur', [], 'admin'),
                'placeholder' => $this->translator->trans('Saisissez un préfixe', [], 'admin'),
                'slug' => $this->translator->trans('Saisissez un suffixe', [], 'admin')
            ],
            'fields_type' => [
                'title' => IntegerType::class
            ],
            'excludes_fields' => ['headerTable']
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