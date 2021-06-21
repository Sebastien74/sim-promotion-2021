<?php

namespace App\Form\Type\Module\Form;

use App\Entity\Module\Form\Calendar;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CalendarType
 *
 * @property TranslatorInterface $translator
 * @property bool $isInternalUser
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CalendarType extends AbstractType
{
    private $translator;
    private $isInternalUser;

    /**
     * CalendarType constructor.
     *
     * @param TranslatorInterface $translator
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        TranslatorInterface $translator,
        AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->translator = $translator;
        $this->isInternalUser = $authorizationChecker->isGranted('ROLE_INTERNAL');
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isNew = !$builder->getData()->getId();

        $adminName = new WidgetType\AdminNameType($this->translator);
        $adminName->add($builder, ['slug-internal' => $this->isInternalUser]);

        if(!$isNew) {

            $builder->add('daysPerPage', Type\IntegerType::class, [
                'label' => $this->translator->trans('Nombre de jour par page', [], 'admin'),
                'attr' => ['data-config' => true, 'group' => 'col-md-3', 'placeholder' => $this->translator->trans("Saisissez un chiffre", [], 'admin')],
                'constraints' => [new Assert\NotBlank()]
            ]);

            $builder->add('frequency', Type\IntegerType::class, [
                'label' => $this->translator->trans('Fréquence', [], 'admin'),
                'attr' => ['data-config' => true, 'group' => 'col-md-3', 'placeholder' => $this->translator->trans("Saisissez un chiffre", [], 'admin')],
                'constraints' => [new Assert\NotBlank()]
            ]);

            $builder->add('minHours', Type\IntegerType::class, [
                'required' => false,
                'label' => $this->translator->trans("Nombre d'heures minimum avant RDV", [], 'admin'),
                'attr' => ['data-config' => true, 'group' => 'col-md-3', 'placeholder' => $this->translator->trans("Saisissez un chiffre", [], 'admin')]
            ]);

            $builder->add('maxHours', Type\IntegerType::class, [
                'required' => false,
                'label' => $this->translator->trans("Nombre d'heures maximum avant RDV", [], 'admin'),
                'attr' => ['data-config' => true, 'group' => 'col-md-3', 'placeholder' => $this->translator->trans("Saisissez un chiffre", [], 'admin')]
            ]);

            $builder->add('startHour', Type\TimeType::class, [
                'required' => false,
                'label' => $this->translator->trans('Heure de début', [], 'admin'),
                'attr' => ['data-config' => true, 'group' => 'col-md-2']
            ]);

            $builder->add('endHour', Type\TimeType::class, [
                'required' => false,
                'label' => $this->translator->trans('Heure de fin', [], 'admin'),
                'attr' => ['data-config' => true, 'group' => 'col-md-2']
            ]);

            $builder->add('receivingEmails', WidgetType\TagInputType::class, [
                'label' => $this->translator->trans("E-mails de réception", [], 'admin'),
                'required' => false,
                'attr' => [
                    'data-config' => true,
                    'group' => 'col-md-8',
                    'placeholder' => $this->translator->trans('Ajouter des e-mails', [], 'admin')
                ]
            ]);

            $builder->add('controls', Type\CheckboxType::class, [
                'label' => $this->translator->trans('Activer les boutons de controles ?', [], 'admin'),
                'attr' => ['data-config' => true, 'group' => 'col-md-3']
            ]);

            $i18ns = new WidgetType\i18nsCollectionType($this->translator);
            $i18ns->add($builder, [
                'website' => $options['website'],
                'data_config' => true,
                'content_config' => false,
                'fields' => ['title' => 'col-md-6', 'subTitle' => 'col-md-6', 'body', 'placeholder' => 'col-12'],
                'excludes_fields' => ['headerTable'],
                'label_fields' => [
                    'title' => $this->translator->trans("Objet du mail de reception", [], 'admin'),
                    'subTitle' => $this->translator->trans("Objet du mail de confirmation", [], 'admin'),
                    'body' => $this->translator->trans("Corps du mail de confirmation", [], 'admin'),
                    'placeholder' => $this->translator->trans('Message de remerciement sur le site', [], 'admin'),
                ],
                'placeholder_fields' => [
                    'title' => $this->translator->trans("Saisissez un objet", [], 'admin'),
                    'subTitle' => $this->translator->trans("Saisissez un objet", [], 'admin'),
                    'body' => $this->translator->trans("Saisissez un message", [], 'admin'),
                    'placeholder' => $this->translator->trans('Saisissez un message', [], 'admin'),
                ]
            ]);

            $builder->add('schedules', Type\CollectionType::class, [
                'label' => false,
                'entry_type' => CalendarScheduleType::class,
                'allow_add' => true,
                'prototype' => true,
                'by_reference' => true,
                'entry_options' => ['attr' => ['button' => false, 'icon' => 'fal fa-calendar']]
            ]);

            $builder->add('exceptions', Type\CollectionType::class, [
                'label' => false,
                'entry_type' => CalendarExceptionType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'prototype' => true,
                'by_reference' => false,
                'entry_options' => ['attr' => ['group' => 'col-md-4', 'icon' => 'fal fa-concierge-bell']]
            ]);
        }

        $save = new WidgetType\SubmitType($this->translator);
        $save->add($builder);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Calendar::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}