<?php

namespace App\Form\Type\Module\Agenda;

use App\Entity\Module\Agenda\Information;
use App\Entity\Module\Agenda\Period;
use App\Form\Widget as WidgetType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * PeriodType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class PeriodType extends AbstractType
{
    private $translator;

    /**
     * PeriodType constructor.
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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Period $data */
        $data = $builder->getData();

        $dates = new WidgetType\PublicationDatesType($this->translator);
        $dates->add($builder, [
            'disabled_default' => true,
            'entity' => $data,
            'uniq_fields' => ['publicationStart', 'publicationEnd'],
            'required_fields' => ['publicationStart', 'publicationEnd'],
            'publicationStart' => $this->translator->trans('Date de début', [], 'admin'),
            'endLabel' => $this->translator->trans('Date de fin', [], 'admin'),
            'startGroup' => 'col-12',
            'endGroup' => 'col-12',
        ]);

        $builder->add('information', EntityType::class, [
            'required' => false,
            'display' => 'search',
            'label' => $this->translator->trans('Informations', [], 'admin'),
            'placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
            'class' => Information::class,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('i')
                    ->orderBy('i.adminName', 'ASC');
            },
            'choice_label' => function ($entity) {
                return strip_tags($entity->getAdminName());
            }
        ]);

        $builder->add('save', Type\SubmitType::class, [
            'label' => $this->translator->trans('Enregistrer', [], 'admin'),
            'attr' => ['class' => 'btn-info ajax-post close-modal refresh', 'group' => 'mt-4 col-12 text-center']
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Period::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}