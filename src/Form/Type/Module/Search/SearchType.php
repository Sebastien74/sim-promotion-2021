<?php

namespace App\Form\Type\Module\Search;

use App\Entity\Module\Search\Search;
use App\Entity\Layout\Page;
use App\Form\Widget as WidgetType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * SearchType
 *
 * @property TranslatorInterface $translator
 * @property EntityManagerInterface $entityManager
 * @property bool $isInternalUser
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SearchType extends AbstractType
{
    private $translator;
    private $entityManager;
    private $isInternalUser;

    /**
     * SearchType constructor.
     *
     * @param TranslatorInterface $translator
     * @param EntityManagerInterface $entityManager
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(TranslatorInterface $translator, EntityManagerInterface $entityManager, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->translator = $translator;
        $this->entityManager = $entityManager;
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

        if (!$isNew) {

            $builder->add('resultsPage', EntityType::class, [
                'required' => false,
                'display' => 'search',
                'label' => $this->translator->trans('Page de resultats', [], 'admin'),
                'placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
                'class' => Page::class,
                'choice_label' => function ($entity) {
                    return strip_tags($entity->getAdminName());
                },
                'attr' => ['group' => 'col-md-3']
            ]);

            $builder->add('itemsPerPage', Type\IntegerType::class, [
                'label' => $this->translator->trans("Nombre de résultats par page", [], 'admin'),
                'attr' => ['group' => 'col-md-3']
            ]);

            $builder->add('orderBy', Type\ChoiceType::class, [
                'label' => $this->translator->trans('Ordonner par', [], 'admin'),
                'display' => 'search',
                'attr' => ['group' => 'col-md-3'],
                'choices' => [
                    $this->translator->trans('Pertinence', [], 'admin') => 'score',
                    $this->translator->trans('Dates (croissantes)', [], 'admin') => 'date-asc',
                    $this->translator->trans('Dates (décroissantes)', [], 'admin') => 'date-desc'
                ]
            ]);

            $builder->add('searchType', Type\ChoiceType::class, [
                'label' => $this->translator->trans("Type de recherche", [], 'admin'),
                'display' => 'search',
                'attr' => ['group' => 'col-md-3'],
                'choices' => [
                    $this->translator->trans('Phrase saisie', [], 'admin') => 'sentence',
                    $this->translator->trans('Tous les mots', [], 'admin') => 'words'
                ]
            ]);

            $builder->add('entities', Type\ChoiceType::class, [
                'label' => $this->translator->trans('Entité(s) recherchée(s)', [], 'admin'),
                'multiple' => true,
                'display' => 'search',
                'choices' => $this->getI18nsEntities(),
                'attr' => [
                    'data-placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
                ]
            ]);

            $builder->add('filterGroup', Type\CheckboxType::class, [
                'required' => false,
                'label' => $this->translator->trans('Afficher les résulats par groupes ?', [], 'admin')
            ]);
        }

        $save = new WidgetType\SubmitType($this->translator);
        $save->add($builder);
    }

    /**
     * Get i18n entities relations
     */
    private function getI18nsEntities()
    {
        $entities = [];
        $metaData = $this->entityManager->getMetadataFactory()->getAllMetadata();

        foreach ($metaData as $data) {

            if ($data->getReflectionClass()->getModifiers() === 0) {

                $classname = $data->getName();
                $entity = new $classname();

                $interface = method_exists($entity, 'getInterface') ? $entity::getInterface() : [];
                $hasI18n = method_exists($entity, 'getI18ns') || method_exists($entity, 'getI18n');
                $inSearch = !empty($interface['search']) && $interface['search'];

                if ($hasI18n && $inSearch) {

                    $interfaceName = method_exists($entity, 'getInterface') && !empty($entity::getInterface()['name'])
                        ? $entity::getInterface()['name'] : NULL;
                    $translation = $interfaceName
                        ? $this->translator->trans('singular', [], 'entity_' . $entity::getInterface()['name'])
                        : $classname;
                    $label = $interfaceName && $translation !== 'singular' ? $translation : $classname;
                    $entities[$label] = $classname;
                }
            }
        }

        $entities['PDF'] = 'pdf';

        return $entities;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Search::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}