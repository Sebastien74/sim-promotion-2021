<?php

namespace App\Form\Widget;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * AosEffectType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class AosEffectType extends AbstractType
{
    private $translator;

    /**
     * AosEffectType constructor.
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
            'display' => 'search',
            'choices' => $this->getAosEffects(),
            'placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
            'label' => $this->translator->trans('Ou effet AOS', [], 'admin'),
        ]);
    }

    /**
     * Get effects
     *
     * @return array
     */
    private function getAosEffects(): array
    {
        $choices = [
            $this->translator->trans('Aucun', [], 'admin') => NULL,
            'fade-up' => 'fade-up',
            'fade-down' => 'fade-down',
            'fade-right' => 'fade-right',
            'fade-left' => 'fade-left',
            'fade-up-right' => 'fade-up-right',
            'fade-up-left' => 'fade-up-left',
            'fade-down-right' => 'fade-down-right',
            'fade-down-left' => 'fade-down-left',
            'flip-left' => 'flip-left',
            'flip-right' => 'flip-right',
            'flip-up' => 'flip-up',
            'flip-down' => 'flip-down',
            'zoom-in' => 'zoom-in',
            'zoom-in-up' => 'zoom-in-up',
            'zoom-in-down' => 'zoom-in-down',
            'zoom-in-left' => 'zoom-in-left',
            'zoom-in-right' => 'zoom-in-right',
            'zoom-out' => 'zoom-out',
            'zoom-out-up' => 'zoom-out-up',
            'zoom-out-down' => 'zoom-out-down',
            'zoom-out-right' => 'zoom-out-right',
            'zoom-out-left' => 'zoom-out-left'
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