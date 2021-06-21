<?php

namespace App\Form\Type\Layout\Management;

use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * MarginType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class MarginType
{
    private $translator;

    /**
     * MarginType constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Add field
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function add(FormBuilderInterface $builder, array $options = [])
    {
        $builder->add('marginTop', Type\ChoiceType::class, [
            'required' => false,
            'display' => 'search',
            'placeholder' => $this->translator->trans('NULL', [], 'admin'),
            'choices' => $this->getSizes('m', 't'),
            'label' => $this->translator->trans('Haute', [], 'admin'),
            'attr' => ['group' => 'col-md-6 mb-md-0', 'class' => 'disable-search'],
        ]);

        $builder->add('marginRight', Type\ChoiceType::class, [
            'required' => false,
            'display' => 'search',
            'placeholder' => $this->translator->trans('NULL', [], 'admin'),
            'choices' => $this->getSizes('m', 'e'),
            'label' => $this->translator->trans('Haute', [], 'admin'),
            'attr' => ['group' => 'col-md-6 mb-md-0', 'class' => 'disable-search'],
        ]);

        $builder->add('marginBottom', Type\ChoiceType::class, [
            'required' => false,
            'display' => 'search',
            'placeholder' => $this->translator->trans('NULL', [], 'admin'),
            'choices' => $this->getSizes('m', 'b'),
            'label' => $this->translator->trans('Basse', [], 'admin'),
            'attr' => ['group' => 'col-md-6 mb-md-0', 'class' => 'disable-search'],
        ]);

        $builder->add('marginLeft', Type\ChoiceType::class, [
            'required' => false,
            'display' => 'search',
            'placeholder' => $this->translator->trans('NULL', [], 'admin'),
            'choices' => $this->getSizes('m', 's'),
            'label' => $this->translator->trans('Haute', [], 'admin'),
            'attr' => ['group' => 'col-md-6 mb-md-0', 'class' => 'disable-search'],
        ]);

        $builder->add('paddingTop', Type\ChoiceType::class, [
            'required' => false,
            'display' => 'search',
            'placeholder' => $this->translator->trans('NULL', [], 'admin'),
            'choices' => $this->getSizes('p', 't'),
            'label' => $this->translator->trans('Haute', [], 'admin'),
            'attr' => ['group' => 'col-md-3 disable-asterisk', 'class' => 'disable-search']
        ]);

        $builder->add('paddingRight', Type\ChoiceType::class, [
            'required' => false,
            'display' => 'search',
            'placeholder' => $this->translator->trans('NULL', [], 'admin'),
            'choices' => $this->getSizes('p', 'e'),
            'label' => $this->translator->trans('Droite', [], 'admin'),
            'attr' => ['group' => 'col-md-3 disable-asterisk', 'class' => 'disable-search']
        ]);

        $builder->add('paddingBottom', Type\ChoiceType::class, [
            'required' => false,
            'display' => 'search',
            'placeholder' => $this->translator->trans('NULL', [], 'admin'),
            'choices' => $this->getSizes('p', 'b'),
            'label' => $this->translator->trans('Basse', [], 'admin'),
            'attr' => ['group' => 'col-md-3 disable-asterisk', 'class' => 'disable-search']
        ]);

        $builder->add('paddingLeft', Type\ChoiceType::class, [
            'required' => false,
            'display' => 'search',
            'placeholder' => $this->translator->trans('NULL', [], 'admin'),
            'choices' => $this->getSizes('p', 's'),
            'label' => $this->translator->trans('Gauche', [], 'admin'),
            'attr' => ['group' => 'col-md-3 disable-asterisk', 'class' => 'disable-search']
        ]);
    }

    /**
     * Get padding sizes
     *
     * @param string $type
     * @param string $position
     * @return array
     */
    private function getSizes(string $type, string $position): array
    {
        $margins = [
            $this->translator->trans('0', [], 'admin') => $type . $position . '-0',
            'XS' => $type . $position . '-xs',
            'S' => $type . $position . '-sm',
            'M' => $type . $position . '-md',
            'L' => $type . $position . '-lg',
            'XL' => $type . $position . '-xl',
            'XXL' => $type . $position . '-xxl'
        ];

        if($type === 'm') {
            $margins = array_merge($margins, [
                'NXS' => $type . $position . '-xs-neg',
                'NS' => $type . $position . '-sm-neg',
                'NM' => $type . $position . '-md-neg',
                'NL' => $type . $position . '-lg-neg',
                'NXL' => $type . $position . '-xl-neg',
                'NXXL' => $type . $position . '-xxl-neg'
            ]);
        }

        return $margins;
    }
}