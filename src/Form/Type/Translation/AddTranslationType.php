<?php

namespace App\Form\Type\Translation;

use App\Entity\Translation\TranslationDomain;
use App\Form\Widget as WidgetType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * AddTranslationType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class AddTranslationType extends AbstractType
{
    private $translator;

    /**
     * AddTranslationType constructor.
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
        $builder->add('domain', EntityType::class, [
            'display' => 'search',
            'label' => $this->translator->trans("Domaine de traduction", [], 'admin'),
            'attr' => [
                'data-placeholder' => $this->translator->trans("Sélectionnez", [], 'admin'),
                'group' => 'col-md-4'
            ],
            'class' => TranslationDomain::class,
            'choice_label' => function ($entity) {
                return strip_tags($entity->getAdminName());
            },
            'constraints' => [new Assert\NotBlank()]
        ]);

        $builder->add('keyName', Type\TextType::class, [
            'label' => $this->translator->trans('Clé de traduction', [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans('Saisissez la clé', [], 'admin'),
                'group' => 'col-md-8'
            ],
            'constraints' => [new Assert\NotBlank()]
        ]);

        $save = new WidgetType\SubmitType($this->translator);
        $save->add($builder, [
            'only_save' => true,
            'has_ajax' => true,
            'refresh' => true
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => NULL,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}