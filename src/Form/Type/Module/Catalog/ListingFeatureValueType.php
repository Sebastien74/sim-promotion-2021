<?php

namespace App\Form\Type\Module\Catalog;

use App\Entity\Core\Website;
use App\Entity\Module\Catalog\FeatureValue;
use App\Entity\Module\Catalog\ListingFeatureValue;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * ListingFeatureValueType
 *
 * @property TranslatorInterface $translator
 * @property Website $website
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ListingFeatureValueType extends AbstractType
{
    private $translator;
    private $website;

    /**
     * ListingFeatureValueType constructor.
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

        $builder->add('value', EntityType::class, [
            'label' => false,
            'class' => FeatureValue::class,
            'placeholder' => $this->translator->trans("Sélectionnez", [], 'admin'),
            'attr' => ['group' => 'col-12 mb-3'],
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('f')
                    ->where('f.website = :website')
                    ->andWhere('f.adminName IS NOT NULL')
                    ->andWhere('f.slug IS NOT NULL')
                    ->setParameter('website', $this->website)
                    ->setParameter('website', $this->website)
                    ->orderBy('f.adminName', 'ASC');
            },
            'group_by' => 'catalogfeature.adminName',
            'choice_label' => function ($entity) {
                return strip_tags($entity->getAdminName());
            },
            'display' => "search",
            'constraints' => [new Assert\NotBlank()]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ListingFeatureValue::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}