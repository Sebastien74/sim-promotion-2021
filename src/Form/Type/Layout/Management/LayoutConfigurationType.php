<?php

namespace App\Form\Type\Layout\Management;

use App\Entity\Core\Module;
use App\Entity\Layout\BlockType;
use App\Entity\Layout\LayoutConfiguration;
use App\Form\Widget as WidgetType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * LayoutConfigurationType
 *
 * @property TranslatorInterface $translator
 * @property EntityManagerInterface $entityManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class LayoutConfigurationType extends AbstractType
{
    private $translator;
    private $entityManager;

    /**
     * LayoutConfigurationType constructor.
     *
     * @param TranslatorInterface $translator
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(TranslatorInterface $translator, EntityManagerInterface $entityManager)
    {
        $this->translator = $translator;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isNew = !$builder->getData()->getId();

        $adminName = new WidgetType\AdminNameType($this->translator);
        $adminName->add($builder, ['adminNameGroup' => $isNew ? 'col-12' : 'col-md-8']);

        if (!$isNew) {

            $builder->add('entity', ChoiceType::class, [
                'label' => $this->translator->trans('Entité', [], 'admin'),
                'placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
                'attr' => ['group' => 'col-md-4'],
                'constraints' => [new NotBlank()],
                'display' => 'search',
                'choices' => $this->getEntities(),
                'choice_translation_domain' => false
            ]);

            $builder->add('modules', EntityType::class, [
                'label' => $this->translator->trans('Modules', [], 'admin'),
                'class' => Module::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('m')
                        ->orderBy('m.adminName', 'ASC');
                },
                'attr' => [
                    'data-placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
                ],
                'choice_label' => function ($entity) {
                    return strip_tags($entity->getAdminName());
                },
                'multiple' => true,
                'required' => false,
                'display' => 'search'
            ]);

            $builder->add('blockTypes', EntityType::class, [
                'label' => $this->translator->trans('Types de blocs', [], 'admin'),
                'class' => BlockType::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('b')
                        ->orderBy('b.adminName', 'ASC');
                },
                'attr' => [
                    'data-placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
                ],
                'choice_label' => function ($entity) {
                    return strip_tags($entity->getAdminName());
                },
                'multiple' => true,
                'required' => false,
                'display' => "select-2"
            ]);
        }

        $save = new WidgetType\SubmitType($this->translator);
        $save->add($builder);
    }

    /**
     * Get Entities
     *
     * @return array
     */
    private function getEntities()
    {
        $entities = [];
        $metaData = $this->entityManager->getMetadataFactory()->getAllMetadata();

        foreach ($metaData as $data) {
            if ($data->getReflectionClass()->getModifiers() === 0) {
                $namespace = $data->getName();
                $entity = new $namespace();
                if (method_exists($entity, 'getLayout')) {
                    $entities[$this->translator->trans($namespace, [], 'entity')] = $namespace;
                }
            }
        }

        return $entities;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => LayoutConfiguration::class,
            'website' => NULL,
            'is_new' => false,
            'translation_domain' => 'admin'
        ]);
    }
}