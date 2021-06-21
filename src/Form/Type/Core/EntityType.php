<?php

namespace App\Form\Type\Core;

use App\Entity\Core\Entity;
use App\Entity\Core\Website;
use App\Form\Widget as WidgetType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * EntityType
 *
 * @property TranslatorInterface $translator
 * @property EntityManagerInterface $entityManager
 * @property Request $request
 * @property Website $website
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class EntityType extends AbstractType
{
    private $translator;
    private $entityManager;
    private $request;
    private $website;

    /**
     * EntityType constructor.
     *
     * @param TranslatorInterface $translator
     * @param EntityManagerInterface $entityManager
     * @param RequestStack $requestStack
     */
    public function __construct(TranslatorInterface $translator, EntityManagerInterface $entityManager, RequestStack $requestStack)
    {
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isNew = !$builder->getData()->getId();
        $data = $builder->getData();
        $properties = $this->getProperties($data);
        $this->website = $options['website'];

        $adminName = new WidgetType\AdminNameType($this->translator);
        $adminName->add($builder, ['adminNameGroup' => 'col-md-4']);

        $builder->add('className', Type\ChoiceType::class, [
            'label' => $this->translator->trans('Classe', [], 'admin'),
            'display' => 'search',
            'choices' => $this->getNamespaces(),
            'placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
            'attr' => ['group' => 'col-md-4']
        ]);

        if (!$isNew) {

            $builder->add('entityId', Type\ChoiceType::class, [
                'label' => $this->translator->trans('Filtre', [], 'admin'),
                'required' => false,
                'display' => 'search',
                'choices' => $this->getFilters($data),
                'placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
                'attr' => ['group' => 'col-md-4']
            ]);

            $builder->add('asCard', Type\CheckboxType::class, [
                'required' => false,
                'display' => 'button',
                'color' => 'outline-dark',
                'label' => $this->translator->trans('Type fiche', [], 'admin'),
                'attr' => ['group' => 'col-md-3', 'class' => 'w-100']
            ]);

            $builder->add('mediaMulti', Type\CheckboxType::class, [
                'required' => false,
                'display' => 'button',
                'color' => 'outline-dark',
                'label' => $this->translator->trans('Multi médias', [], 'admin'),
                'attr' => ['group' => 'col-md-3', 'class' => 'w-100']
            ]);

            $builder->add('uniqueLocale', Type\CheckboxType::class, [
                'required' => false,
                'display' => 'button',
                'color' => 'outline-dark',
                'label' => $this->translator->trans('Langue unique', [], 'admin'),
                'attr' => ['group' => 'col-md-3', 'class' => 'w-100']
            ]);

            $builder->add('inFieldConfiguration', Type\CheckboxType::class, [
                'required' => false,
                'display' => 'button',
                'color' => 'outline-dark',
                'label' => $this->translator->trans('Ajouter au sélecteur du module formulaire', [], 'admin'),
                'attr' => ['group' => 'col-md-3', 'class' => 'w-100']
            ]);

            $builder->add('orderBy', Type\ChoiceType::class, [
                'label' => $this->translator->trans('Ordonner par', [], 'admin'),
                'display' => 'search',
                'choices' => $properties,
                'placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
                'attr' => ['group' => 'col-md-4']
            ]);

            $builder->add('orderSort', Type\ChoiceType::class, [
                'label' => $this->translator->trans('Trier par ordre', [], 'admin'),
                'display' => 'search',
                'choices' => [
                    $this->translator->trans('Croissant', [], 'admin') => 'ASC',
                    $this->translator->trans('Décroissant', [], 'admin') => 'DESC'
                ],
                'placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
                'attr' => ['group' => 'col-md-4']
            ]);

            $builder->add('adminLimit', Type\IntegerType::class, [
                'label' => $this->translator->trans('Admin limite', [], 'admin'),
                'attr' => [
                    'placeholder' => $this->translator->trans('Saisissez une limite', [], 'admin'),
                    'group' => 'col-md-4'
                ]
            ]);

            $builder->add('columns', Type\ChoiceType::class, [
                'label' => $this->translator->trans("Colonnes de l'index", [], 'admin'),
                'attr' => [
                    'data-placeholder' => $this->translator->trans('Sélectionnez', [], 'admin')
                ],
                'display' => 'search',
                'multiple' => true,
                'required' => false,
                'choices' => $properties
            ]);

            $builder->add('searchFields', Type\ChoiceType::class, [
                'label' => $this->translator->trans("Colonnes à rechercher dans l'index", [], 'admin'),
                'attr' => [
                    'data-placeholder' => $this->translator->trans('Sélectionnez', [], 'admin')
                ],
                'display' => 'search',
                'multiple' => true,
                'required' => false,
                'choices' => $properties,
                'help' => $this->translator->trans("Moteur de recherche administration", [], 'admin')
            ]);

            $builder->add('searchFilters', Type\ChoiceType::class, [
                'label' => $this->translator->trans("Colonnes à rechercher par filtres", [], 'admin'),
                'attr' => [
                    'data-placeholder' => $this->translator->trans('Sélectionnez', [], 'admin')
                ],
                'display' => 'search',
                'multiple' => true,
                'required' => false,
                'choices' => $properties,
                'help' => $this->translator->trans("Formulaire de recherche des index (modal)", [], 'admin')
            ]);

            $builder->add('exports', Type\ChoiceType::class, [
                'label' => $this->translator->trans('Colonnes à Exporter', [], 'admin'),
                'attr' => [
                    'data-placeholder' => $this->translator->trans('Sélectionnez', [], 'admin')
                ],
                'multiple' => true,
                'required' => false,
                'display' => 'search',
                'choices' => $properties
            ]);

            $builder->add('saveArea', Type\ChoiceType::class, [
                'label' => $this->translator->trans('Affichage des boutons', [], 'admin'),
                'placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
                'required' => false,
                'display' => 'search',
                'choices' => [
                    $this->translator->trans('En haut', [], 'admin') => 'top',
                    $this->translator->trans('En bas', [], 'admin') => 'bottom',
                    $this->translator->trans('Les deux', [], 'admin') => 'both',
                ]
            ]);

            $builder->add('showView', Type\ChoiceType::class, [
                'label' => $this->translator->trans("Afficher dans la visualisation", [], 'admin'),
                'attr' => [
                    'data-placeholder' => $this->translator->trans('Sélectionnez', [], 'admin')
                ],
                'display' => 'search',
                'multiple' => true,
                'required' => false,
                'choices' => $properties
            ]);
        }

        $save = new WidgetType\SubmitType($this->translator);
        $save->add($builder);
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
                $namespaces[$this->translator->trans($namespace, [], 'entity')] = $namespace;
            }
        }

        return $namespaces;
    }

    /**
     * Get entities to filter
     *
     * @param Entity $configuration
     * @return array
     */
    private function getFilters(Entity $configuration)
    {
        $choices = [];
        $className = $configuration->getClassName();
        $defaultLocale = $this->request->get('locale');

        if (!empty($className) && class_exists($configuration->getClassName())) {

            $repository = $this->entityManager->getRepository($configuration->getClassName());
            $entity = new $className();
            $masterField = method_exists($entity, 'getMasterfield') ? $entity::getMasterfield() : NULL;
            $masterFieldSetter = 'get' . ucfirst($masterField);

            if (!$masterField) {
                $entities = $repository->findAll();
            } elseif ($masterField === 'website' || method_exists($entity, 'getWebsite')) {
                $entities = $repository->findByWebsite($this->website);
            } elseif ($entity::getMasterfield() && method_exists($entity->$masterFieldSetter(), 'getWebsite')) {
                $entities = $repository->createQueryBuilder('e')
                    ->join('e.' . $masterField, $masterField)
                    ->where($masterField . '.website' . ' = :website')
                    ->setParameter(':website', $this->website)
                    ->addSelect($masterField)
                    ->getQuery()
                    ->getResult();
            }

            if (!empty($entities)) {
                foreach ($entities as $entity) {
                    $title = method_exists($entity, 'getAdminName') ? $entity->getAdminName() : $entity->getId();
                    if (empty($title) && method_exists($entity, 'getI18ns')) {
                        foreach ($entity->getI18ns() as $i18n) {
                            if ($i18n->getLocale() === $defaultLocale) {
                                $title = $i18n->getTitle();
                            }
                        }
                    }
                    if (empty($title)) {
                        $title = $this->translator->trans("ID :", [], 'admin') . $entity->getId();
                    }
                    $choices[$title] = $entity->getId();
                }
            }
        }

        return $choices;
    }

    /**
     * Get properties
     *
     * @param Entity $configuration
     * @return array
     */
    private function getProperties(Entity $configuration)
    {
        $excludes = ['entityConfiguration', 'createdBy', 'updatedBy', 'mediaRelations', 'icon'];
        $choices = ['infos' => 'infos'];
        $className = $configuration->getClassName();

        if (!empty($className) && class_exists($className)) {

            $fieldsMapping = $this->entityManager->getClassMetadata($configuration->getClassName())->getFieldNames();
            foreach ($fieldsMapping as $key => $field) {
                $choices[$field] = $field;
            }

            $associationsMapping = $this->entityManager->getClassMetadata($configuration->getClassName())->getAssociationNames();
            foreach ($associationsMapping as $field) {
                $associationClass = $this->entityManager->getClassMetadata($configuration->getClassName())->getAssociationTargetClass($field);
                $fieldsMapping = $this->entityManager->getClassMetadata($associationClass)->getFieldNames();
                $choices[$field] = $field;
                foreach ($fieldsMapping as $fieldMapping) {
                    if (!in_array($field, $excludes)) {
                        $choices[$field . '.' . $fieldMapping] = $field . '.' . $fieldMapping;
                    }
                    if ($field === 'mediaRelations') {
                        $choices['media'] = 'media';
                    }
                }
            }
        }

        if (!isset($choices['adminName'])) {
            $choices['adminName'] = 'adminName';
        }

        return $choices;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Entity::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}