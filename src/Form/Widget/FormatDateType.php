<?php

namespace App\Form\Widget;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * FormatDateType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FormatDateType extends AbstractType
{
    private $translator;

    /**
     * FormatDateType constructor.
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
            'label' => $this->translator->trans('Format de date', [], 'admin'),
            'required' => false,
            'display' => 'search',
            'empty_data' => "dd/MM/Y",
            'attr' => [
                'group' => "col-md-3",
                'data-config' => true
            ],
            'choices' => [
                $this->translator->trans("jj/mm", [], 'admin') => "dd/MM",
                $this->translator->trans("jj/mm/aaaa", [], 'admin') => "dd/MM/Y",
                $this->translator->trans("jour jj/mm/aaaa", [], 'admin') => "cccc dd/MM/Y",
                $this->translator->trans("jour jj mois aaaa", [], 'admin') => "cccc dd MMMM Y",
                $this->translator->trans("jj mois aaaa", [], 'admin') => "dd MMMM Y",
                $this->translator->trans("jj/mm/aaaa hh:mm", [], 'admin') => "dd/MM/Y à HH:mm",
                $this->translator->trans("jour jj/mm/aaaa hh:mm", [], 'admin') => "cccc dd/MM/Y à HH:mm",
                $this->translator->trans("jour jj mois aaaa hh:mm", [], 'admin') => "cccc dd MMMM Y à HH:mm",
                $this->translator->trans("jj mois aaaa hh:mm", [], 'admin') => "dd MMMM Y à HH:mm",
            ]
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getParent(): ?string
    {
        return ChoiceType::class;
    }
}