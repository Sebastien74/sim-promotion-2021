<?php

namespace App\Form\Type\Layout\Block;

use App\Entity\Layout\Block;
use App\Form\Widget as WidgetTypes;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * WidgetType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class WidgetType extends AbstractType
{
    private $translator;

    /**
     * WidgetType constructor.
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
        $i18ns = new WidgetTypes\i18nsCollectionType($this->translator);
        $i18ns->add($builder, [
            'website' => $options['website'],
            'fields' => ['introduction'],
            'label_fields' => ['introduction' => $this->translator->trans('Script', [], 'admin')],
            'placeholder_fields' => ['introduction' => $this->translator->trans('Saisissez un script', [], 'admin')],
            'content_config' => false
        ]);

        $builder->add('controls', Type\CheckboxType::class, [
            'required' => false,
            'label' => $this->translator->trans('Activer la vérification RGPD ?', [], 'admin'),
            'attr' => ['group' => 'col-md-3']
        ]);

        $save = new WidgetTypes\SubmitType($this->translator);
        $save->add($builder, [ 'btn_back' => true ]);
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