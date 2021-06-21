<?php

namespace App\Form\Type\Development;

use App\Entity\Todo\Task;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class TaskType extends AbstractType
{
    private $translator;

    /**
     * FaqType constructor.
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
        $builder->add('done', Type\CheckboxType::class, [
            'label' => false,
            'attr' => ['group' => 'col-1 todo-task-done text-center p-0']
        ]);

        $builder->add('adminName', Type\TextType::class, [
            'label' => false,
            'attr' => [
                'placeholder' => $this->translator->trans('Saisissez une tâche', [], 'admin'),
                'group' => 'col-12 todo-task'
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
            'website' => NULL,
            'prototypePosition' => true,
            'translation_domain' => 'admin'
        ]);
    }
}