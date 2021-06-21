<?php

namespace App\Form\Type\Layout\Management;

use App\Entity\Core\Transition;
use App\Entity\Core\Website;
use App\Repository\Core\TransitionRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * TransitionType
 *
 * @property TranslatorInterface $translator
 * @property Website $website
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TransitionType
{
    private $translator;
    private $website;

    /**
     * TransitionType constructor.
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
        $this->website = isset($options['website']) && $options['website'] instanceof Website ? $options['website'] : NULL;

        if($this->website) {

            $builder->add('transition', EntityType::class, [
                'required' => false,
                'label' => $this->translator->trans('Éffet', [], 'admin'),
                'display' => 'search',
                'class' => Transition::class,
                'placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
                'attr' => ['group' => 'col-md-4'],
                'query_builder' => function (TransitionRepository $repository) {
                    return $repository->createQueryBuilder('t')
                        ->andWhere('t.isActive = :isActive')
                        ->andWhere('t.configuration = :configuration')
                        ->andWhere('t.section IS NULL')
                        ->setParameter('isActive', true)
                        ->setParameter('configuration', $this->website->getConfiguration())
                        ->orderBy('t.adminName', 'ASC');
                },
                'choice_label' => function ($entity) {
                    return strip_tags($entity->getAdminName());
                }
            ]);

            $builder->add('delay', Type\IntegerType::class, [
                'required' => false,
                'label' => $this->translator->trans('Délai avant apparition', [], 'admin'),
                'attr' => [
                    'group' => 'col-md-4',
                    'placeholder' => $this->translator->trans('Saisissez un délai', [], 'admin')
                ],
                'help' => $this->translator->trans('Optionnel', [], 'admin')
            ]);

            $builder->add('duration', Type\IntegerType::class, [
                'required' => false,
                'label' => $this->translator->trans('Durée de la transition', [], 'admin'),
                'attr' => [
                    'group' => 'col-md-4',
                    'placeholder' => $this->translator->trans('Saisissez une durée', [], 'admin')
                ],
                'help' => $this->translator->trans('Optionnel', [], 'admin')
            ]);
        }
    }
}