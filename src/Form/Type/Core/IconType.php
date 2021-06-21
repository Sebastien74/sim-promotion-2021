<?php

namespace App\Form\Type\Core;

use App\Entity\Core\Icon;
use App\Form\Validator\File;
use App\Form\Validator\UniqFile;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * IconType
 *
 * @property TranslatorInterface $translator
 *
 * @property string MAX_SIZE
 * @property string ACCEPT
 * @property array MIME_TYPES
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class IconType extends AbstractType
{
    private $translator;

    private const MAX_SIZE = '200M';
    private const ACCEPT = '.svg';
    private const MIME_TYPES = ['image/svg+xml'];

    /**
     * IconType constructor.
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
        $builder->add('uploadedFile', FileType::class, [
            'label' => false,
            'mapped' => false,
            'multiple' => true,
            'required' => false,
            'attr' => [
                'accept' => self::ACCEPT,
                'data-max-size' => self::MAX_SIZE,
                'placeholder' => $this->translator->trans('Séléctionnez une image', [], 'admin'),
                'class' => 'dropzone-field',
                'group' => 'd-none',
                'data-height' => 250
            ],
            'constraints' => [
                new File([
                    'maxSize' => self::MAX_SIZE,
                    'mimeTypes' => self::MIME_TYPES
                ])
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => NULL,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}