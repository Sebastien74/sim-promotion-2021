<?php

namespace App\Form\Widget;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * LaxEffectType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class LaxEffectType extends AbstractType
{
    private $translator;

    /**
     * LaxEffectType constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'required' => false,
            'multiple' => true,
            'display' => 'search',
            'choices' => $this->getLaxEffects(),
            'placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
            'label' => $this->translator->trans('Effets Lax', [], 'admin'),
        ]);
    }

    /**
     * Get effects
     *
     * @return array
     */
    private function getLaxEffects(): array
    {
        $choices = [
            'linger' => 'linger',
            'lazy' => 'lazy',
            'eager' => 'eager',
            'slalom' => 'slalom',
            'crazy' => 'crazy',
            'spin' => 'spin',
            'spinRev' => 'spinRev',
            'spinIn' => 'spinIn',
            'spinOut' => 'spinOut',
            'blurInOut' => 'blurInOut',
            'blurIn' => 'blurIn',
            'blurOut' => 'blurOut',
            'fadeInOut' => 'fadeInOut',
            'fadeIn' => 'fadeIn',
            'fadeOut' => 'fadeOut',
            'driftLeft' => 'driftLeft',
            'driftRight' => 'driftRight',
            'leftToRight' => 'leftToRight',
            'rightToLeft' => 'rightToLeft',
            'zoomInOut' => 'zoomInOut',
            'zoomIn' => 'zoomIn',
            'zoomOut' => 'zoomOut',
            'swing' => 'swing',
            'speedy' => 'speedy'
        ];

        ksort($choices);

        return $choices;
    }

    /**
     * {@inheritDoc}
     */
    public function getParent(): ?string
    {
        return ChoiceType::class;
    }
}