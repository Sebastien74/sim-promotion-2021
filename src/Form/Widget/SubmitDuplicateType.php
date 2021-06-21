<?php

namespace App\Form\Widget;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType as SymfonySubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * SubmitDuplicateType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SubmitDuplicateType extends AbstractType
{
    private $translator;

    /**
     * SubmitDuplicateType constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Generate submit Type
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function add(FormBuilderInterface $builder, array $options = [])
    {
        $builder->add('save', SymfonySubmitType::class, [
            'label' => $this->translator->trans('Dupliquer', [], 'admin'),
            'attr' => [
                'class' => 'btn btn-outline-white'
            ]
        ]);
    }
}