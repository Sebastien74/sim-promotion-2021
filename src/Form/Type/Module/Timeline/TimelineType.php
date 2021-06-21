<?php

namespace App\Form\Type\Module\Timeline;

use App\Entity\Module\Timeline\Timeline;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * TimelineType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TimelineType extends AbstractType
{
    private $translator;

    /**
     * TimelineType constructor.
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
        $adminName = new WidgetType\AdminNameType($this->translator);
        $adminName->add($builder, [
            'slug' => true,
            'class' => 'refer-code'
        ]);

        $save = new WidgetType\SubmitType($this->translator);
        $save->add($builder);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Timeline::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}