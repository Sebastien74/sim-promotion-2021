<?php

namespace App\Form\Type\Development;

use App\Entity\Todo\Todo;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * TodoType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TodoType extends AbstractType
{
    private $translator;

    /**
     * FaqType constructor.
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
        $isNew = !$builder->getData()->getId();

        $adminName = new WidgetType\AdminNameType($this->translator);
        $adminName->add($builder);

        if (!$isNew) {
            $builder->add('tasks', CollectionType::class, [
                'label' => false,
                'entry_type' => TaskType::class,
                'allow_add' => true,
                'prototype' => true,
                'by_reference' => false,
                'entry_options' => [
                    'attr' => [
                        'class' => 'task',
                        'icon' => 'fal fa-clipboard-list-check',
                        'caption' => $this->translator->trans('Liste des tâches', [], 'admin'),
                        'button' => $this->translator->trans('Ajouter une tâche', [], 'admin')
                    ],
                    'website' => $options['website']
                ]
            ]);
        }

        $save = new WidgetType\SubmitType($this->translator);
        $save->add($builder);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Todo::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}