<?php

namespace App\Form\Type\Core\Init;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * DataBaseType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class DataBaseType extends AbstractType
{
    private $translator;

    /**
     * DataBaseType constructor.
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
        $builder->add('host', Type\TextType::class, [
            'label' => $this->translator->trans("Hôte", [], 'core_init'),
            'display' => 'floating',
            'data' => 'localhost',
            'constraints' => [new Assert\NotBlank()]
        ]);

        $builder->add('user', Type\TextType::class, [
            'label' => $this->translator->trans("Utilisateur", [], 'core_init'),
            'display' => 'floating',
            'data' => 'root',
            'constraints' => [new Assert\NotBlank()]
        ]);

        $builder->add('password', Type\TextType::class, [
            'required' => false,
            'label' => $this->translator->trans("Mot de passe", [], 'core_init'),
            'display' => 'floating'
        ]);

        $builder->add('db_name', Type\TextType::class, [
            'label' => $this->translator->trans("Nom de la base de données", [], 'core_init'),
            'display' => 'floating',
            'constraints' => [new Assert\NotBlank()]
        ]);

        $builder->add('port', Type\TextType::class, [
            'label' => $this->translator->trans("Port", [], 'core_init'),
            'display' => 'floating',
            'data' => '3308',
            'constraints' => [new Assert\NotBlank()]
        ]);

        $builder->add('prefix', Type\TextType::class, [
            'label' => $this->translator->trans("Préfixe des tables", [], 'core_init'),
            'display' => 'floating',
            'attr' => ['maxlength' => 3],
            'constraints' => [new Assert\NotBlank(), new Assert\Length(['min' => 3, 'max' => 3])]
        ]);

        $builder->add('save', Type\SubmitType::class, [
            'label' => $this->translator->trans("Enregistrer", [], 'core_init'),
            'attr' => ['class' => 'btn-primary mt-4']
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

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return "project_init";
    }
}