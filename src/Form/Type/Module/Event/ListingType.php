<?php

namespace App\Form\Type\Module\Event;

use App\Entity\Module\Event\Category;
use App\Entity\Module\Event\Listing;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * ListingType
 *
 * @property TranslatorInterface $translator
 * @property bool $isInternalUser
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ListingType extends AbstractType
{
    private $translator;
    private $isInternalUser;

    /**
     * ListingType constructor.
     *
     * @param TranslatorInterface $translator
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(TranslatorInterface $translator, AuthorizationCheckerInterface $authorizationChecker)
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

        if (!$isNew) {

            $builder->add('categories', EntityType::class, [
                'required' => false,
                'display' => 'search',
                'label' => $this->translator->trans("Catégories", [], 'admin'),
                'attr' => [
                    'data-placeholder' => $this->translator->trans("Sélectionnez", [], 'admin')
                ],
                'class' => Category::class,
                'choice_label' => function ($entity) {
                    return strip_tags($entity->getAdminName());
                },
                'multiple' => true
            ]);
        }

        if (!$isNew && $this->isInternalUser) {

            $builder->add('orderBy', Type\ChoiceType::class, [
                'label' => $this->translator->trans('Ordonner les actualités par', [], 'admin'),
                'display' => 'search',
                'attr' => ['group' => 'col-md-4', 'data-config' => true],
                'choices' => [
//                    $this->translator->trans('Positions des catégories (croissantes)', [], 'admin') => 'category-position-asc',
//                    $this->translator->trans('Positions des catégories (décroissantes)', [], 'admin') => 'category-position-desc',
//                    $this->translator->trans('Titres des catégories (croissants)', [], 'admin') => 'category-title-asc',
//                    $this->translator->trans('Titres des catégories (décroissants)', [], 'admin') => 'category-title-desc',
                    $this->translator->trans('Dates (croissantes)', [], 'admin') => 'publicationStart-asc',
                    $this->translator->trans('Dates (décroissantes)', [], 'admin') => 'publicationStart-desc',
                    $this->translator->trans('Positions (croissantes)', [], 'admin') => 'position-asc',
                    $this->translator->trans('Positions (décroissantes)', [], 'admin') => 'position-desc'
                ]
            ]);

            $builder->add('formatDate', WidgetType\FormatDateType::class, [
                'attr' => ['group' => 'col-md-4', 'data-config' => true]
            ]);

            $builder->add('itemsPerPage', Type\IntegerType::class, [
                'required' => false,
                'label' => $this->translator->trans("Nombre d'actualités par page", [], 'admin'),
                'attr' => ['group' => 'col-md-4', 'data-config' => true]
            ]);

            $builder->add('hideDate', Type\CheckboxType::class, [
                'required' => false,
                'label' => $this->translator->trans("Cacher la date ?", [], 'admin'),
                'attr' => ['group' => 'col-md-2', 'data-config' => true]
            ]);

            $builder->add('displayCategory', Type\CheckboxType::class, [
                'required' => false,
                'label' => $this->translator->trans("Afficher le nom de la catégorie ?", [], 'admin'),
                'attr' => ['group' => 'col-md-3', 'data-config' => true]
            ]);

            $builder->add('displayThumbnail', Type\CheckboxType::class, [
                'required' => false,
                'label' => $this->translator->trans("Afficher les vignettes ?", [], 'admin'),
                'attr' => ['group' => 'col-md-3', 'data-config' => true]
            ]);

            $builder->add('largeFirst', Type\CheckboxType::class, [
                'required' => false,
                'label' => $this->translator->trans("Mettre en avant la dernière actualité ?", [], 'admin'),
                'attr' => ['group' => 'col-md-3', 'data-config' => true]
            ]);

            $builder->add('scrollInfinite', Type\CheckboxType::class, [
                'required' => false,
                'label' => $this->translator->trans("Scroll infinite ?", [], 'admin'),
                'attr' => ['group' => 'col-md-2', 'data-config' => true]
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
            'data_class' => Listing::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}