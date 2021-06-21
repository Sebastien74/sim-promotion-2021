<?php

namespace App\Form\Type\Module\Catalog;

use App\Entity\Module\Catalog\FeatureValue;
use App\Entity\Module\Catalog\FeatureValueProduct;
use App\Entity\Module\Catalog\Product;
use App\Entity\Core\Website;
use App\Form\Widget as WidgetType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * FeatureValueProductType
 *
 * @property TranslatorInterface $translator
 * @property Website $website
 * @property Product $product
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FeatureValueProductType extends AbstractType
{
    private $translator;
    private $website;
    private $product;

    /**
     * FeatureValueProductType constructor.
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
        $this->website = $options['website'];
        $this->product = $options['product'];

        $builder->add('value', EntityType::class, [
            'label' => false,
            'required' => false,
            'display' => 'search',
            'placeholder' => $this->translator->trans("Sélectionnez une valeur", [], 'admin'),
            'group_by' => 'catalogfeature.adminName',
            'class' => FeatureValue::class,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('v')
                    ->andWhere('v.website = :website')
                    ->andWhere('v.slug IS NOT NULL')
                    ->andWhere('v.product IS NULL')
                    ->orWhere('v.product = :product')
                    ->setParameter('product', $this->product)
                    ->setParameter('website', $this->website)
                    ->orderBy('v.adminName', 'ASC');
            },
            'choice_label' => function ($entity) {
                return strip_tags($entity->getAdminName());
            }
        ]);

        $builder->add('feature', WidgetType\FeatureType::class);

        $builder->add('adminName', Type\TextType::class, [
            'label' => false,
            'attr' => [
                'placeholder' => $this->translator->trans('Éditez la valeur', [], 'admin')
            ]
        ]);

        $builder->add('displayInArray', Type\CheckboxType::class, [
            'required' => false,
            'label' => $this->translator->trans('Afficher dans un tableau ?', [], 'admin'),
            'attr' => ['group' => 'col-md-3 mt-2']
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FeatureValueProduct::class,
            'website' => NULL,
            'product' => NULL,
            'custom_widget' => true,
            'translation_domain' => 'admin'
        ]);
    }
}