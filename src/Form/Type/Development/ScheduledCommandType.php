<?php

namespace App\Form\Type\Development;

use App\Entity\Core\ScheduledCommand;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * ScheduledCommandType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ScheduledCommandType extends AbstractType
{
    private $translator;

    /**
     * ScheduledCommandType constructor.
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
        $isNew = !$builder->getData()->getId();

        $adminName = new WidgetType\AdminNameType($this->translator);
        $adminName->add($builder, ['adminNameGroup' => $isNew ? 'col-md-6' : 'col-md-4']);

        $builder->add('command', CommandChoiceType::class, [
            'label' => $this->translator->trans('Commande', [], 'admin'),
            'placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
            'display' => 'search',
            'attr' => ['group' => $isNew ? 'col-md-6' : 'col-md-4']
        ]);

        $builder->add('cronExpression', Type\TextType::class, [
            'label' => $this->translator->trans('Expression cron', [], 'admin'),
            'attr' => [
                'group' => $isNew ? 'col-md-6' : 'col-md-4',
                'placeholder' => $this->translator->trans("*/10 * * * *", [], 'admin')
            ],
            'help' => '<a href="http://www.abunchofutils.com/utils/developer/cron-expression-helper/" target="_blank">' . $this->translator->trans('Générer', [], 'admin') . '</a>'
        ]);

        $builder->add('description', Type\TextType::class, [
            'label' => $this->translator->trans('Description', [], 'admin'),
            'attr' => [
                'group' => $isNew ? 'col-md-6' : 'col-md-9',
                'placeholder' => $this->translator->trans("Saisissez une description*", [], 'admin')
            ]
        ]);

        if (!$isNew) {

            $builder->add('logFile', Type\TextType::class, [
                'required' => false,
                'label' => $this->translator->trans('Nom du fichier de log', [], 'admin'),
                'attr' => [
                    'group' => 'col-md-3',
                    'placeholder' => $this->translator->trans("Saisissez un nom", [], 'admin')
                ]
            ]);

            $builder->add('executeImmediately', Type\CheckboxType::class, [
                'required' => false,
                'label' => $this->translator->trans('Exécuter maintenant ?', [], 'admin'),
                'attr' => ['group' => 'col-md-3']
            ]);

            $builder->add('active', Type\CheckboxType::class, [
                'required' => false,
                'label' => $this->translator->trans('Activé ?', [], 'admin'),
                'attr' => ['group' => 'col-md-2']
            ]);

            $builder->add('locked', Type\CheckboxType::class, [
                'required' => false,
                'label' => $this->translator->trans('Bloqué suite erreur ?', [], 'admin'),
                'attr' => ['group' => 'col-md-3']
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
            'data_class' => ScheduledCommand::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}