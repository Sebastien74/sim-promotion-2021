<?php

namespace App\Form\Widget;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType as SymfonySubmitType;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * SubmitType
 *
 * @property TranslatorInterface $translator
 * @property string $ajaxClass
 * @property string $refreshClass
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SubmitType
{
    private $translator;
    private $ajaxClass;
    private $refreshClass;

    /**
     * SubmitType constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Generate submit Types
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function add(FormBuilderInterface $builder, array $options = [])
    {
        $data = $builder->getData();
        $isNew = method_exists($data, 'getId') && !$data->getId() || !method_exists($data, 'getId');

        $this->ajaxClass = !empty($options['has_ajax']) ? ' ajax-post' : '';
        $this->refreshClass = !empty($options['refresh']) ? ' refresh' : '';

        if (!empty($options['only_save'])) {
            $this->saveButton($builder, $isNew, $options);
        } elseif (!empty($options['btn_back'])) {
            $this->saveBack($builder, $options);
        } elseif ($data && $isNew) {
            $this->newButtons($builder, $options);
        } elseif ($data) {
            $this->saveButton($builder, $isNew, $options);
        }

        if (!empty($options['btn_both']) && !$isNew) {
            $this->saveBack($builder, $options);
        }
    }

    /**
     * New buttons
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    private function newButtons(FormBuilderInterface $builder, array $options = [])
    {
        $builder->add('save', SymfonySubmitType::class, [
            'label' => $this->translator->trans('Enregistrer', [], 'admin'),
            'attr' => [
                'class' => 'btn-info' . $this->ajaxClass . $this->refreshClass
            ]
        ]);

        $builder->add('saveEdit', SymfonySubmitType::class, [
            'label' => $this->translator->trans('Enregistrer et éditer', [], 'admin'),
            'attr' => [
                'class' => 'btn-info' . $this->ajaxClass . $this->refreshClass
            ]
        ]);
    }

    /**
     * Save buttons
     *
     * @param FormBuilderInterface $builder
     * @param bool $isNew
     * @param array $options
     */
    private function saveButton(FormBuilderInterface $builder, bool $isNew, array $options = [])
    {
        $label = !empty($options['btn_save_label']) ? $options['btn_save_label'] : $this->translator->trans('Enregistrer', [], 'admin');
        $builder->add('save', SymfonySubmitType::class, [
            'label' => $label,
            'attr' => [
                'class' => isset($options['class']) ? $options['class'] . $this->ajaxClass . $this->refreshClass : 'btn-info' . $this->ajaxClass . $this->refreshClass,
                'force' => isset($options['force']) ? $options['force'] : false
            ]
        ]);
    }

    /**
     * Save and go back buttons
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    private function saveBack(FormBuilderInterface $builder, array $options = [])
    {
        $label = !empty($options['btn_both'])
            ? $this->translator->trans('Enregistrer et retourner à la liste', [], 'admin')
            : $this->translator->trans('Enregistrer', [], 'admin');

        $builder->add('saveBack', SymfonySubmitType::class, [
            'label' => $label,
            'attr' => ['class' => 'btn-info']
        ]);
    }
}