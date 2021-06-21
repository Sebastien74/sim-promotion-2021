<?php

namespace App\Form\Type\Module\Catalog;

use App\Form\Widget as WidgetType;
use App\Entity\Module\Catalog\Feature;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * FeatureType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FeatureType extends AbstractType
{
    private $translator;

    /**
     * FeatureType constructor.
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
        $isNew = !$builder->getData()->getId();

        $adminName = new WidgetType\AdminNameType($this->translator);
        $adminName->add($builder);

        if (!$isNew) {

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
        $save->add($builder);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Feature::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}