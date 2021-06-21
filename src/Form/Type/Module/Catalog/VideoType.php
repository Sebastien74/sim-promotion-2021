<?php

namespace App\Form\Type\Module\Catalog;

use App\Form\Widget as WidgetType;
use App\Entity\Module\Catalog\Video;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * VideoType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class VideoType extends AbstractType
{
    private $translator;

    /**
     * VideoType constructor.
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
        $adminName = new WidgetType\AdminNameType($this->translator);
        $adminName->add($builder, [
            'label' => false,
            'placeholder' => $this->translator->trans('Lien de la vidéo (Youtube, Vimeo, Dailymotion)', [], 'admin'),
            'constrains' => [
                new Assert\NotBlank(),
                new Assert\Url()
            ]
        ]);

        $i18ns = new WidgetType\i18nsCollectionType($this->translator);
        $i18ns->add($builder, [
            'fields' => ['video'],
            'placeholder_fields' => [
                'video' => $this->translator->trans('Lien de la vidéo (Youtube, Vimeo, Dailymotion)', [], 'admin')
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Video::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}