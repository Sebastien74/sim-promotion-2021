<?php

namespace App\Form\Type\Media;

use App\Entity\Layout\BlockType;
use App\Entity\Media\ThumbAction;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ThumbActionType
 *
 * @property TranslatorInterface $translator
 * @property EntityManagerInterface $entityManager
 * @property KernelInterface $kernel
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ThumbActionType extends AbstractType
{
    private $translator;
    private $entityManager;
    private $kernel;

    /**
     * ThumbActionType constructor.
     *
     * @param TranslatorInterface $translator
     * @param EntityManagerInterface $entityManager
     * @param KernelInterface $kernel
     */
    public function __construct(TranslatorInterface $translator, EntityManagerInterface $entityManager, KernelInterface $kernel)
    {
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->kernel = $kernel;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('adminName', Type\TextType::class, [
            'label' => $this->translator->trans('Intitulé', [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans('Saisissez un intitulé', [], 'admin'),
                'group' => 'col-md-8'
            ]
        ]);

        $builder->add('namespace', Type\ChoiceType::class, [
            'label' => $this->translator->trans('Namespace', [], 'admin'),
            'choices' => $this->getNamespaces(),
            'display' => 'search',
            'placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
            'attr' => [
                'group' => 'col-md-4'
            ],
            'constraints' => [new Assert\NotBlank()]
        ]);

        $builder->add('action', Type\ChoiceType::class, [
            'label' => $this->translator->trans('Action', [], 'admin'),
            'required' => false,
            'display' => 'search',
            'choices' => $this->getActions(),
            'placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
            'attr' => [
                'group' => 'col-md-4'
            ]
        ]);

        $builder->add('actionFilter', Type\TextType::class, [
            'label' => $this->translator->trans('Filtre (id ou slug)', [], 'admin'),
            'required' => false,
            'attr' => [
                'placeholder' => $this->translator->trans('Saisissez un id', [], 'admin'),
                'group' => 'col-md-4'
            ]
        ]);

        $builder->add('blockType', EntityType::class, [
            'label' => $this->translator->trans('Type de bloc', [], 'admin'),
            'required' => false,
            'display' => 'search',
            'placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
            'attr' => ['group' => 'col-md-4'],
            'class' => BlockType::class,
            'query_builder' => function (EntityRepository $er) {

                $slugs = ['titleheader', 'modal', 'media', 'card'];
                $statement = $er->createQueryBuilder('b');

                foreach ($slugs as $key => $slug) {
                    $condition = $key === 0 ? 'andWhere' : 'orWhere';
                    $statement->$condition('b.slug = :slug' . $key)
                        ->setParameter('slug' . $key, $slug);
                }

                return $statement->orderBy('b.adminName', 'ASC');
            },
            'choice_label' => function ($entity) {
                return strip_tags($entity->getAdminName());
            }
        ]);
    }

    /**
     * Get namespaces
     *
     * @return array
     */
    private function getNamespaces()
    {
        $namespaces = [];
        $metaData = $this->entityManager->getMetadataFactory()->getAllMetadata();
        foreach ($metaData as $data) {
            $namespace = $data->getName();
            if ($data->getReflectionClass()->getModifiers() === 0) {
                $entity = new $namespace();
                if (method_exists($entity, 'getMediaRelations')) {
                    $namespaces[$this->translator->trans($namespace, [], 'entity')] = $namespace;
                }
            }
        }

        return $namespaces;
    }

    /**
     * Get actions
     *
     * @return array
     */
    private function getActions()
    {
        $excludes = ['getSubscribedServices', '__construct', 'setContainer'];
        $projectDir = $this->kernel->getProjectDir();
        $options = [];

        $frontDirCore = $projectDir . '/src/Controller/Front/Action';
        $finderControllers = new Finder();
        $finderControllers->files()->in($frontDirCore);
        foreach ($finderControllers as $file) {
            $relativeName = $file->getRelativePathname();
            $controllerName = str_replace('.php', '', $relativeName);
            $methods = get_class_methods('\App\\Controller\Front\\Action\\' . $controllerName);
            foreach ($methods as $method) {
                if (!in_array($method, $excludes)) {
                    $options[$method] = $method;
                }
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
            'data_class' => ThumbAction::class,
            'legend' => $this->translator->trans('Actions', [], 'admin'),
            'button' => $this->translator->trans('Ajouter une action', [], 'admin'),
            'translation_domain' => 'admin',
            'website' => NULL
        ]);
    }
}