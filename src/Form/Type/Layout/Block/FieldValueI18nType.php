<?php

namespace App\Form\Type\Layout\Block;

use App\Entity\Translation\i18n;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * FieldValueI18nType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FieldValueI18nType extends AbstractType
{
    private $translator;

    /**
     * FieldValueI18nType constructor.
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
        $builder->add('introduction', Type\TextType::class, [
            'required' => false,
            'label' => $this->translator->trans("Label", [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans("Saisissez un label", [], 'admin'),
                'group' => 'col-md-6'
            ]
        ]);

        $builder->add('body', Type\TextType::class, [
            'required' => false,
            'label' => $this->translator->trans("Valeur", [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans("Saisissez une valeur", [], 'admin'),
                'group' => 'col-md-6'
            ]
        ]);
    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => i18n::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}