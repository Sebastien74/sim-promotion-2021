<?php

namespace App\Form\Type\Module\Form;

use App\Entity\Module\Form\ContactValue;
use Exception;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CalendarAppointmentContactValuesType
 *
 * @property TranslatorInterface $translator
 * @property FrontType $frontType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CalendarAppointmentContactValuesType extends AbstractType
{
    private $translator;
    private $frontType;

    /**
     * CalendarAppointmentContactValuesType constructor.
     *
     * @param TranslatorInterface $translator
     * @param FrontType $frontType
     */
    public function __construct(TranslatorInterface $translator, FrontType $frontType)
    {
        $this->translator = $translator;
        $this->frontType = $frontType;
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $disabled = ['["true"]', FileType::class];
        $values = $options['collection']->getValues();
        $value = !empty($values[$builder->getName()]) ? $values[$builder->getName()] : NULL;

        if($value instanceof ContactValue) {

            $fieldConfiguration = $value->getConfiguration();
            $block = $fieldConfiguration->getBlock();
            $fieldType = $block->getBlockType()->getFieldType();

            if(!in_array($value->getValue(), $disabled) &&  !in_array($fieldType, $disabled)) {
                $this->frontType->setField($fieldType, 'value', $block, $builder, $value, true, true);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ContactValue::class,
            'website' => NULL,
            'collection' => [],
            'translation_domain' => 'admin'
        ]);
    }
}