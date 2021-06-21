<?php

namespace App\Form\Type\Layout\Management;

use App\Entity\Layout\Col;
use App\Entity\Layout\Zone;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * ScreensType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ScreensType
{
    private $translator;

    /**
     * ScreensType constructor.
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
        $entity = isset($options['entity']) && $options['entity'] ? $options['entity'] : NULL;
        $count = $entity instanceof Zone ? $entity->getCols()->count() : ($entity instanceof Col ? $entity->getBlocks()->count() : 0);

        if($count) {

            $choices = [];
            $choices[$this->translator->trans('Par défault', [], 'admin')] = NULL;
            for ($x = 1; $x <= $count; $x++) {
                $choices[$x] = $x;
            }

            $builder->add('mobilePosition', ChoiceType::class, [
                'required' => false,
                'label' => isset($options['mobilePositionLabel']) ? $this->translator->trans('Ordre sur mobile', [], 'admin') : false,
                'display' => 'search',
                'choices' => $choices,
                'attr' => ['group' => isset($options['mobilePositionGroup']) ? $options['mobilePositionGroup'] : 'col-md-6']
            ]);

            $builder->add('tabletPosition', ChoiceType::class, [
                'required' => false,
                'label' => isset($options['tabletPositionLabel']) ? $this->translator->trans('Ordre sur tablette', [], 'admin') : false,
                'display' => 'search',
                'choices' => $choices,
                'attr' => ['group' => isset($options['tabletPositionGroup']) ? $options['tabletPositionGroup'] : 'col-md-6']
            ]);
        }

        $sizeChoices = [];
        $sizeChoices[$this->translator->trans('Par défault', [], 'admin')] = NULL;
        for ($i = 1; $i <= 12; $i++) {
            $sizeChoices[$i] = $i;
        }

        $builder->add('mobileSize', ChoiceType::class, [
            'label' => isset($options['mobileSizeLabel']) ? $this->translator->trans('Taille sur mobile', [], 'admin') : false,
            'required' => false,
            'choices' => $sizeChoices,
            'display' => 'search',
            'attr' => ['group' => isset($options['mobileSizeGroup']) ? $options['mobileSizeGroup'] : 'col-md-6']
        ]);

        $builder->add('tabletSize', ChoiceType::class, [
            'label' => isset($options['tabletSizeLabel']) ? $this->translator->trans('Taille sur tablette', [], 'admin') : false,
            'required' => false,
            'choices' => $sizeChoices,
            'display' => 'search',
            'attr' => ['group' => isset($options['tabletSizeGroup']) ? $options['mobileSizeGroup'] : 'col-md-6']
        ]);
    }
}