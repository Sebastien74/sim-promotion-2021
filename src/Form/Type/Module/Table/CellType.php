<?php

namespace App\Form\Type\Module\Table;

use App\Entity\Module\Table\Cell;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CellType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CellType extends AbstractType
{
    private $translator;

    /**
     * CellType constructor.
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
        $i18ns = new WidgetType\i18nsCollectionType($this->translator);
        $i18ns->add($builder, [
            'label' => false,
            'fields' => ['title', 'introduction', 'body'],
            'excludes_fields' => ['headerTable'],
            'title_force' => true,
            'content_config' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Cell::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}