<?php

namespace App\Form\Type\Core\Init;

use App\Form\Widget as WidgetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * InternationalizationType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class InternationalizationType extends AbstractType
{
    private $translator;

    /**
     * InternationalizationType constructor.
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
        $builder->add('locale', WidgetType\LanguageIconType::class, [
            'label' => $this->translator->trans("Langue principale", [], 'core_init'),
            'constraints' => [new Assert\NotBlank()]
        ]);

        $builder->add('locales_others', WidgetType\LanguageIconType::class, [
            'label' => $this->translator->trans("Autres langues", [], 'core_init'),
            'required' => false,
            'multiple' => true,
            'attr' => [
                'data-placeholder' => $this->translator->trans("Sélectionnez", [], 'core_init'),
            ]
        ]);

        $builder->add('save', Type\SubmitType::class, [
            'label' => $this->translator->trans("Enregistrer", [], 'core_init'),
            'attr' => ['class' => 'btn-primary mt-4']
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => NULL,
            'translation_domain' => 'admin'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return "project_init";
    }
}