<?php

namespace App\Form\Type\Module\Table;

use App\Entity\Module\Table\Col;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * ColType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ColType extends AbstractType
{
    private $translator;

    /**
     * ColType constructor.
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
            'fields' => ['title'],
            'label_fields' => ['title' => false],
            'title_force' => true,
            'content_config' => false,
        ]);

        $builder->add('cells', CollectionType::class, [
            'label' => false,
            'entry_type' => CellType::class,
            'entry_options' => ['website' => $options['website']]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Col::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}