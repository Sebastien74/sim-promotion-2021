<?php

namespace App\Form\Widget;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * EffectDelayType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class EffectDelayType extends AbstractType
{
    private $translator;

    /**
     * EffectDelayType constructor.
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
            'attr' => [
                'placeholder' => $this->translator->trans('Saisissez un délai', [], 'admin'),
                'group' => 'col-md-6 mb-md-0'
            ],
            'label' => $this->translator->trans("Délai", [], 'admin')
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getParent(): ?string
    {
        return TextType::class;
    }
}