<?php

namespace App\Form\Type\Seo;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ImportRedirectionType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ImportRedirectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('xls_file', Type\FileType::class, [
            'label' => false,
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
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}