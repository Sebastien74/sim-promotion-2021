<?php

namespace App\Form\Type\Translation;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ImportType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ImportType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('files', FileType::class, [
            'label' => false,
            'multiple' => true,
            'attr' => ['accept' => '.xlsx'],
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\File([
                    'mimeTypes' => ["application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"]
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
            'translation_domain' => 'admin'
        ]);
    }
}