<?php

namespace App\Form\Widget;

use App\Entity\Module\Form\Calendar;
use App\Entity\Module\Form\Form;
use App\Repository\Module\Form\CalendarRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * FormCalendarsType
 *
 * @property TranslatorInterface $translator
 * @property OptionsResolver $resolver
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FormCalendarsType extends AbstractType
{
    private $translator;
    private $resolver;
    private $repository;

    /**
     * FormCalendarsType constructor.
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
        $this->resolver = $resolver;

        $resolver->setDefaults([
            'label' => false,
            'required' => false,
            'form' => NULL,
            'display' => 'search',
            'placeholder' => $this->translator->trans("Sélectionnez", [], 'admin'),
            'class' => Calendar::class,
            'query_builder' => function (CalendarRepository $repository) {
                $this->repository = $repository;
                $this->resolver->setNormalizer('form', function (Options $options, Form $form) {
                    return $this->repository->createQueryBuilder('c')
                        ->leftJoin('c.form', 'f')
                        ->andWhere('c.form = :form')
                        ->setParameter('form', $form)
                        ->orderBy('c.position', 'ASC')
                        ->addSelect('c');
                });
            },
            'choice_label' => 'adminName'
        ]);

        $this->resolver->setNormalizer('form', function (Options $options, Form $form) {
            if($form->getCalendars()->count() === 1) {
                $this->resolver->setDefaults([
                    'attr' => ['group' => 'd-none']
                ]);
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): ?string
    {
        return EntityType::class;
    }
}