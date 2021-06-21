<?php

namespace App\Form\Type\Layout\Management;

use App\Entity\Layout\Block;
use App\Entity\Layout\Col;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * ColBlocksType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ColBlocksType extends AbstractType
{
    private $translator;

    /**
     * ColBlocksType constructor.
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
        $margins = new ScreensType($this->translator);
        $margins->add($builder, [
            'entity' => $options['col'],
            'mobilePositionLabel' => $options['mobilePositionLabel'],
            'tabletPositionLabel' => $options['tabletPositionLabel'],
            'mobileSizeLabel' => $options['mobileSizeLabel'],
            'tabletSizeLabel' => $options['tabletSizeLabel']
        ]);

        $builder->add('alignment', AlignmentType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Block::class,
            'mobilePositionLabel' => true,
            'tabletPositionLabel' => true,
            'mobilePositionGroup' => 'col-md-6',
            'tabletPositionGroup' =>'col-md-6',
            'mobileSizeLabel' => true,
            'tabletSizeLabel' => true,
            'mobileSizeGroup' => 'col-md-6',
            'tabletSizeGroup' =>'col-md-6',
            'website' => NULL,
            'col' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}