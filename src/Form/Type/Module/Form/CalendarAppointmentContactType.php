<?php

namespace App\Form\Type\Module\Form;

use App\Entity\Module\Form\ContactForm;
use App\Form\Type\Translation\TranslationType;
use App\Form\Validator as Validator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CalendarAppointmentContactType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CalendarAppointmentContactType extends AbstractType
{
    private $translator;

    /**
     * CalendarAppointmentContactType constructor.
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
        $builder->add('contactValues', CollectionType::class, [
            'entry_type' => CalendarAppointmentContactValuesType::class,
            'entry_options' => ['collection' => $options['form_data']->getContactValues()],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ContactForm::class,
            'website' => NULL,
            'form_data' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}