<?php

namespace App\Form\Type\Layout\Management;

use App\Entity\Core\Module;
use App\Entity\Layout\Action;
use App\Form\Widget as WidgetType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * ActionType
 *
 * @property TranslatorInterface $translator
 * @property KernelInterface $kernel
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ActionType extends AbstractType
{
    private $translator;
    private $kernel;

    /**
     * ActionType constructor.
     *
     * @param TranslatorInterface $translator
     * @param KernelInterface $kernel
     */
    public function __construct(TranslatorInterface $translator, KernelInterface $kernel)
    {
        $this->translator = $translator;
        $this->kernel = $kernel;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = $builder->getData();
        $files = $this->getFiles();
        $isNew = !$data->getId();


        $adminName = new WidgetType\AdminNameType($this->translator);
        $adminName->add($builder, [
            'slug' => true,
            'adminNameGroup' => $isNew ? 'col-12' : 'col-md-6',
            'slugGroup' => 'col-md-3'
        ]);

        if (!$isNew) {

            $builder->add('controller', Type\ChoiceType::class, [
                'label' => $this->translator->trans('Controller', [], 'admin'),
                'display' => 'search',
                'placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
                'attr' => ['group' => 'col-md-3'],
                'constraints' => [new Assert\NotBlank()],
                'choices' => $files->controllers,
                'choice_translation_domain' => false
            ]);

            $builder->add('action', Type\ChoiceType::class, [
                'label' => $this->translator->trans('Action', [], 'admin'),
                'display' => 'search',
                'placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
                'attr' => ['group' => 'col-md-3'],
                'constraints' => [new Assert\NotBlank()],
                'choices' => $files->methods,
                'choice_translation_domain' => false
            ]);

            $builder->add('entity', Type\ChoiceType::class, [
                'required' => false,
                'display' => 'search',
                'label' => $this->translator->trans('Filtre', [], 'admin'),
                'placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
                'attr' => ['group' => 'col-md-3'],
                'choices' => $files->entities,
                'choice_translation_domain' => false
            ]);

            $builder->add('module', EntityType::class, [
                'label' => $this->translator->trans('Module', [], 'admin'),
                'display' => 'search',
                'class' => Module::class,
                'placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
                'attr' => ['group' => 'col-md-3'],
                'constraints' => [new Assert\NotBlank()],
                'choice_label' => function ($entity) {
                    return strip_tags($entity->getAdminName());
                }
            ]);

            $builder->add('iconClass', WidgetType\FontawesomeType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'select-icons',
                    'group' => $isNew ? 'col-md-4' : 'col-md-3'
                ]
            ]);

            $builder->add('isCard', Type\CheckboxType::class, [
                'required' => false,
                'display' => 'button',
                'color' => 'outline-dark',
                'label' => $this->translator->trans('Activer le type fiche', [], 'admin'),
                'attr' => ['group' => 'col-md-3', 'class' => 'w-100']
            ]);

            $builder->add('dropdown', Type\CheckboxType::class, [
                'required' => false,
                'display' => 'button',
                'color' => 'outline-dark',
                'label' => $this->translator->trans('Ajouter à la zone non prioritaire', [], 'admin'),
                'attr' => ['group' => 'col-md-3', 'class' => 'w-100']
            ]);
        }

        $save = new WidgetType\SubmitType($this->translator);
        $save->add($builder);
    }

    /**
     * Get files namespaces
     *
     * @return object
     */
    private function getFiles()
    {
        $projectDir = $this->kernel->getProjectDir();

        $options = [];
        $options = $this->getCoreControllers($options, $projectDir);
        $options = $this->getCoreEntities($options, $projectDir);

        unset($options['methods']['robots']);
        unset($options['methods']['xml']);
        unset($options['methods']['xml']);
        unset($options['methods']['toolBox']);
        unset($options['methods']['trackEmails']);
        unset($options['methods']['preview']);

        return (object)$options;
    }

    /**
     * Get Core Controller
     *
     * @param array $options
     * @param string $projectDir
     * @return array
     */
    private function getCoreControllers(array $options, string $projectDir)
    {
        $excludesMethods = ['getSubscribedServices', 'block', '__construct', 'setContainer'];
        $dirname = $projectDir . '/src/Controller/Front/Action';
        $finderControllers = new Finder();
        $finderControllers->files()->in($dirname);

        foreach ($finderControllers as $file) {

            $relativeName = $file->getRelativePathname();
            $controllerName = str_replace('.php', '', $relativeName);
            $namespace = 'App\\Controller\Front\\Action\\' . $controllerName;
            $options['controllers']['Front: ' . $controllerName] = $namespace;
            $methods = get_class_methods('\\' . $namespace);

            foreach ($methods as $method) {
                if (!in_array($method, $excludesMethods)) {
                    $options['methods'][$method] = $method;
                }
            }
        }

        return $options;
    }

    /**
     * Get Core Entities
     *
     * @param array $options
     * @param string $projectDir
     * @return array
     */
    private function getCoreEntities(array $options, string $projectDir)
    {
        $dirname = $projectDir . '/src/Entity';
        $finderActions = new Finder();
        $finderActions->files()->in($dirname);

        foreach ($finderActions as $file) {

            $explodeEntity = explode('/', $file->getRelativePathname());

            if (empty($explodeEntity[1])) {
                $explodeEntity = explode('\\', $file->getRelativePathname());
            }

            if (!empty($explodeEntity) && !empty($explodeEntity[2])) {
                $className = 'App\\Entity\\' . str_replace('.php', '', $file->getRelativePathname());
                $options['entities']['Actions: ' . $explodeEntity[1]][str_replace('.php', '', $explodeEntity[1] . '\\' . $explodeEntity[2])] = str_replace('/', '\\', $className);
            }
        }

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Action::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}