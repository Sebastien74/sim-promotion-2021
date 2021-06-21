<?php

namespace App\Form\Type\Module\Tab;

use App\Entity\Module\Tab\Content;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * ContentType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ContentType extends AbstractType
{
    private $translator;

    /**
     * ContentType constructor.
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
        $adminName->add($builder, ['data_config' => true]);

        if (!$isNew) {

            $mediaRelations = new WidgetType\MediaRelationsCollectionType($this->translator);
            $mediaRelations->add($builder, ['data_config' => true]);

            $i18ns = new WidgetType\i18nsCollectionType($this->translator);
            $i18ns->add($builder, [
                'fields' => ['subTitle' => 'col-12', 'title', 'introduction', 'body'],
                'excludes_fields' => ['headerTable'],
                'label_fields' => [
                    'subTitle' => $this->translator->trans("Intitulé de l'onglet", [], 'admin')
                ]
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
            'data_class' => Content::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}