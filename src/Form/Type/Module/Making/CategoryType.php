<?php

namespace App\Form\Type\Module\Making;

use App\Entity\Module\Making\Category;
use Symfony\Component\Form\AbstractType;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CategoryType
 *
 * @property TranslatorInterface $translator
 * @property bool $isInternalUser
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CategoryType extends AbstractType
{
    private $translator;
    private $isInternalUser;

    /**
     * CategoryType constructor.
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
        $adminName->add($builder);

        if (!$isNew && $this->isInternalUser) {

            $builder->add('orderBy', Type\ChoiceType::class, [
                'label' => $this->translator->trans('Ordonner les actualités par', [], 'admin'),
                'display' => 'search',
                'attr' => ['group' => 'col-md-4', 'data-config' => true],
                'choices' => [
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

            $builder->add('isDefault', Type\CheckboxType::class, [
                'required' => false,
                'label' => $this->translator->trans("Catégorie principale ?", [], 'admin'),
                'attr' => ['group' => 'col-md-2', 'data-config' => true]
            ]);

            $builder->add('mainMediaInHeader', Type\CheckboxType::class, [
                'required' => false,
                'label' => $this->translator->trans("Afficher l'image principale dans les entêtes ?", [], 'admin'),
                'attr' => ['group' => 'col-md-3', 'data-config' => true]
            ]);
        }

        if (!$isNew) {

            $mediaRelations = new WidgetType\MediaRelationsCollectionType($this->translator);
            $mediaRelations->add($builder, [
                'data_config' => true,
                'entry_options' => ['onlyMedia' => true]
            ]);

            $i18ns = new WidgetType\i18nsCollectionType($this->translator);
            $i18ns->add($builder, [
                'website' => $options['website'],
                'fields' => ['title' => 'col-md-8', 'targetLabel' => 'col-md-4'],
                'label_fields' => ['targetLabel' => $this->translator->trans("Label du lien de retour", [], 'admin')],
                'target_config' => false,
                'content_config' => false
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
            'data_class' => Category::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}