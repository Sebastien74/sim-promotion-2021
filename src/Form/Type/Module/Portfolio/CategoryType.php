<?php

namespace App\Form\Type\Module\Portfolio;

use App\Entity\Module\Portfolio\Category;
use Symfony\Component\Form\AbstractType;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CategoryType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CategoryType extends AbstractType
{
    private $translator;

    /**
     * CategoryType constructor.
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
        /** @var Category $category */
        $category = $builder->getData();
        $isNew = !$category->getId();

        $adminName = new WidgetType\AdminNameType($this->translator);
        $adminName->add($builder);

        if(!$isNew) {

            $urls = new WidgetType\UrlsCollectionType($this->translator);
            $urls->add($builder, ['display_seo' => true]);

            if($category->getLayout()->getZones()->count() === 0) {

                $mediaRelations = new WidgetType\MediaRelationsCollectionType($this->translator);
                $mediaRelations->add($builder, [
                    'data_config' => true,
                    'entry_options' => ['onlyMedia' => true]
                ]);

                $i18ns = new WidgetType\i18nsCollectionType($this->translator);
                $i18ns->add($builder, [
                    'website' => $options['website'],
                    'fields' => ['title' => 'col-md-8', 'introduction', 'body', 'targetLabel' => 'col-md-4'],
                    'label_fields' => ['targetLabel' => $this->translator->trans("Label du lien de retour", [], 'admin')],
                    'target_config' => false,
                    'content_config' => false
                ]);
            }

            $dates = new WidgetType\PublicationDatesType($this->translator);
            $dates->add($builder);
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