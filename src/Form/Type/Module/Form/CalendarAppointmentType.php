<?php

namespace App\Form\Type\Module\Form;

use App\Entity\Module\Form\CalendarAppointment;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CalendarAppointmentType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CalendarAppointmentType extends AbstractType
{
    private $translator;

    /**
     * CalendarAppointmentType constructor.
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
        /** @var CalendarAppointment $appointment */
        $appointment = $builder->getData();

        $builder->add('appointmentDate', Type\DateTimeType::class, [
            'label' => $this->translator->trans('Heure du rendez-vous', [], 'admin'),
            'attr' => ['group' => 'col-md-3'],
            'constraints' => [new Assert\NotBlank()]
        ]);

        $builder->add('contactForm', CalendarAppointmentContactType::class, ['form_data' => $appointment->getContactForm()]);

        $save = new WidgetType\SubmitType($this->translator);
        $save->add($builder);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CalendarAppointment::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}