<?php

namespace App\Form\Type\Core;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * DefaultType
 *
 * @property EntityManagerInterface $entityManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class DefaultType extends AbstractType
{
    private $entityManager;

    /**
     * DefaultType constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $excludes = ['id'];
        $properties = $this->entityManager->getClassMetadata($options['data_class'])->getReflectionProperties();
        $associationMappings = $this->entityManager->getClassMetadata($options['data_class'])->getAssociationMappings();

        foreach ($properties as $property => $reflexionProperty) {
            if (empty($associationMappings[$property]) && !in_array($property, $excludes)) {
                $builder->add($property, NULL, [
                    'label' => ucfirst($property)
                ]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}