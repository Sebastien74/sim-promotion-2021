<?php

namespace App\Form\Type\Layout\Management;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * AlignmentType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class AlignmentType extends AbstractType
{
    private $translator;

    /**
     * AlignmentType constructor.
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
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => $this->translator->trans('Alignement des contenus', [], 'admin'),
            'required' => false,
            'display' => 'search',
            'placeholder' => $this->translator->trans('Par défaut', [], 'admin'),
            'choices' => [
                $this->translator->trans("Gauche", [], 'admin') => "start",
                $this->translator->trans("Centré", [], 'admin') => "center",
                $this->translator->trans("Droite", [], 'admin') => "end",
                $this->translator->trans("Justifié", [], 'admin') => "justify"
            ]
        ]);
    }
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}