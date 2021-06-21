<?php

namespace App\Form\Type\Module\Catalog;

use App\Entity\Core\Website;
use App\Entity\Module\Catalog\FeatureValueProduct;
use App\Entity\Module\Catalog\Listing;
use App\Service\Content\CatalogSearchService;
use App\Twig\Translation\i18nRuntime;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * FrontSearchFiltersType
 *
 * @property EntityManagerInterface $entityManager
 * @property Request $request
 * @property CatalogSearchService $searchService
 * @property TranslatorInterface $translator
 * @property i18nRuntime $i18nExtension
 * @property array $options
 * @property array $products
 * @property Website $website
 * @property Listing $listing
 * @property array $translations
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FrontSearchFiltersType extends AbstractType
{
    private $entityManager;
    private $request;
    private $searchService;
    private $translator;
    private $i18nExtension;
    private $options = [];
    private $website;
    private $listing;
    private $products = [];
    private $translations = [];

    /**
     * FrontSearchFiltersType constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param RequestStack $requestStack
     * @param CatalogSearchService $searchService
     * @param TranslatorInterface $translator
     * @param i18nRuntime $i18nExtension
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        RequestStack $requestStack,
        CatalogSearchService $searchService,
        TranslatorInterface $translator,
        i18nRuntime $i18nExtension)
    {
        $this->entityManager = $entityManager;
        $this->request = $requestStack->getCurrentRequest();
        $this->searchService = $searchService;
        $this->translator = $translator;
        $this->i18nExtension = $i18nExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->options = $options;
        $this->website = $options['website'];
        $this->listing = $builder->getData();
        $this->products = $options['products'];

        $this->addSelect($builder, 'categories');
        $this->addSelect($builder, 'catalogs');
        $this->setFieldFeatures($builder);
    }

    /**
     * To add select
     */
    private function addSelect(FormBuilderInterface $builder, string $keyName)
    {
        $configuration = $this->getFieldConfiguration($keyName);
        $choices = $this->getChoices($keyName, $this->products['initial']);

        if ($configuration && $choices) {
            $this->addField($builder, $keyName, $configuration, $choices);
        }
    }

    /**
     * To set Field of Features
     */
    private function setFieldFeatures(FormBuilderInterface $builder)
    {
        $configuration = $this->getFieldConfiguration('features');
        $choices = $this->getValues($this->products['initialResults'], $this->products['initial']);

        if ($configuration && $choices) {
            if ($configuration['multiple']) {
                $this->addField($builder, 'values', $configuration, $choices);
            } else {
                foreach ($choices as $name => $selectChoices) {
                    $this->addField($builder, $name, $configuration, $selectChoices);
                }
            }
        }
    }

    /**
     * To add field
     */
    private function addField(FormBuilderInterface $builder, string $name, array $configuration, array $choices)
    {
        $data = !empty($this->options['filters'][$name]) ? $this->options['filters'][$name] : NULL;

        $builder->add(Urlizer::urlize($name), Type\ChoiceType::class, [
            'label' => !empty($this->translations[$name]) ? $this->translations[$name] : false,
            'required' => false,
            'data' => $data,
            'multiple' => $configuration['multiple'],
            'expanded' => $configuration['expanded'],
            'choices' => $choices,
            'mapped' => false,
            'attr' => [
                'class' => !$configuration['multiple'] ? 'select-search' : NULL,
                'group' => $configuration['multiple'] ? 'col-12' : 'p-0 col-12'
            ],
            'placeholder' => $configuration['placeholder'] ? $this->translator->trans('Sélectionnez', [], 'front_form') : false,
        ]);
    }

    /**
     * To get choices
     */
    private function getChoices(string $keyName, array $fields): array
    {
        $choices = [];

        if (!empty($fields[$keyName])) {
            foreach ($fields[$keyName] as $category) {
                $i18n = $this->i18nExtension->i18n($category);
                $label = $i18n && $i18n->getTitle() ? $i18n->getTitle() : $category->getAdminName();
                $choices[ucfirst(trim($label))] = $category->getSlug();
            }
        }

        return $choices;
    }

    /**
     * To get values
     */
    private function getValues(array $products, array $fields): array
    {
        $choices = [];

        if (!empty($fields['products-values'])) {
            foreach ($fields['products-values'] as $slug => $values) {
                foreach ($values as $productValue) {

                    /** @var FeatureValueProduct $productValue */
                    $value = $productValue->getValue();

                    if ($value->getSlug()) {

                        $feature = $value->getCatalogfeature();
                        $featureSlug = $feature->getSlug();
                        $i18nFeature = $this->i18nExtension->i18n($feature);
                        $this->translations[$featureSlug] = $i18nFeature && $i18nFeature->getTitle()
                            ? ucfirst(trim($i18nFeature->getTitle())) : ucfirst(trim($feature->getAdminName()));

                        $i18nValue = $this->i18nExtension->i18n($value);
                        $label = $i18nValue && $i18nValue->getTitle() ? $i18nValue->getTitle() : $value->getAdminName();

                        $choices[$feature->getSlug()][ucfirst(trim($label))] = $value->getSlug();
                    }
                }
            }
        }

        return $choices;
    }

    /**
     * Get field configuration
     *
     * @param string $propertyName
     * @return array
     */
    private function getFieldConfiguration(string $propertyName): array
    {
        $getter = 'getSearch' . ucfirst($propertyName);
        $property = $this->listing->$getter();

        if ($this->listing->getDisplay() == 'all') {
            return ['multiple' => false, 'expanded' => false, 'display' => 'search', 'placeholder' => true];
        } elseif ($property === 'select-multiple') {
            return ['multiple' => true, 'expanded' => false, 'display' => 'search', 'placeholder' => true];
        } elseif ($property === 'select-uniq') {
            return ['multiple' => false, 'expanded' => false, 'display' => 'search', 'placeholder' => true];
        } elseif ($property === 'radios') {
            return ['multiple' => false, 'expanded' => true, 'display' => false, 'placeholder' => false];
        } elseif ($property === 'checkboxes') {
            return ['multiple' => true, 'expanded' => true, 'display' => false, 'placeholder' => false];
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver, $options = [])
    {
        $resolver->setDefaults([
            'data_class' => Listing::class,
            'filters' => NULL,
            'translation_domain' => 'front_form',
            'products' => [],
            'website' => NULL,
            'csrf_protection' => false
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return "filters_products";
    }
}