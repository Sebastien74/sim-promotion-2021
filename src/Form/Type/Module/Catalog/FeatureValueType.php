<?php

namespace App\Form\Type\Module\Catalog;

use App\Entity\Core\Website;
use App\Form\Widget as WidgetType;
use App\Entity\Module\Catalog\Feature;
use App\Entity\Module\Catalog\FeatureValue;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * FeatureValueType
 *
 * @property TranslatorInterface $translator
 * @property Website $website
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FeatureValueType extends AbstractType
{
    private $translator;
    private $website;

    /**
     * FeatureValueType constructor.
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
        /* @var FeatureValue $data */
        $data = $builder->getData();
        $isNew = !$data->getId();
        $this->website = $options['website'];

        $adminName = new WidgetType\AdminNameType($this->translator);
        $adminName->add($builder, [
            'adminNameGroup' => $isNew ? 'col-12' : 'col-md-6'
        ]);

        if (!$isNew) {

            $builder->add('catalogfeature', EntityType::class, [
                'label' => $this->translator->trans("Caractéristique", [], 'admin'),
                'class' => Feature::class,
                'attr' => [
                    'group' => 'col-md-3',
                    'data-placeholder' => $this->translator->trans("Sélectionnez", [], 'admin')
                ],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->where('p.website = :website')
                        ->setParameter('website', $this->website)
                        ->orderBy('p.adminName', 'ASC');
                },
                'choice_label' => function ($entity) {
                    return strip_tags($entity->getAdminName());
                },
                'display' => "search",
                'constraints' => [new Assert\NotBlank()]
            ]);

            $builder->add('iconClass', WidgetType\FontawesomeType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'select-icons',
                    'group' => 'col-md-3'
                ]
            ]);

            $builder->add('featureBeforePost', Type\HiddenType::class, [
                'mapped' => false,
                'data' => $data->getCatalogfeature()->getId()
            ]);

            $i18ns = new WidgetType\i18nsCollectionType($this->translator);
            $i18ns->add($builder, [
                'content_config' => false,
                'fields' => ['title'],
            ]);

            $mediaRelations = new WidgetType\MediaRelationsCollectionType($this->translator);
            $mediaRelations->add($builder, [
                'data_config' => true,
                'entry_options' => ['onlyMedia' => true]
            ]);
        }

        $save = new WidgetType\SubmitType($this->translator);
        $save->add($builder, ['btn_back' => $isNew ? false : true]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FeatureValue::class,
            'website' => NULL,
            'product' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}