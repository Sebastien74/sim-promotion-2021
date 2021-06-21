<?php

namespace App\Form\Type\Core\Init;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * ConfigurationType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ConfigurationType extends AbstractType
{
    private $translator;

    /**
     * ConfigurationType constructor.
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
        $builder->add('company_name', Type\TextType::class, [
            'label' => $this->translator->trans("Raison sociale", [], 'core_init'),
            'display' => 'floating',
            'constraints' => [new Assert\NotBlank()]
        ]);

        $builder->add('locale_domain', Type\TextType::class, [
            'label' => $this->translator->trans("URL Locale", [], 'core_init'),
            'display' => 'floating',
            'attr' => ['group' => 'col-md-4'],
            'constraints' => [new Assert\NotBlank()]
        ]);

        $builder->add('pre_prod_domain', Type\TextType::class, [
            'label' => $this->translator->trans("URL de pré-production", [], 'core_init'),
            'required' => false,
            'display' => 'floating',
            'attr' => ['group' => 'col-md-4'],
        ]);

        $builder->add('prod_domain', Type\TextType::class, [
            'label' => $this->translator->trans("URL de production", [], 'core_init'),
            'required' => false,
            'display' => 'floating',
            'attr' => ['group' => 'col-md-4'],
        ]);

        $builder->add('google_ua', Type\TextType::class, [
            'label' => $this->translator->trans("Google UA", [], 'core_init'),
            'required' => false,
            'display' => 'floating',
            'attr' => ['group' => 'col-md-6'],
        ]);

        $builder->add('google_tag_manager', Type\TextType::class, [
            'label' => $this->translator->trans("Google Tag manager", [], 'core_init'),
            'required' => false,
            'display' => 'floating',
            'attr' => ['group' => 'col-md-6'],
        ]);

        $builder->add('token_security', Type\TextType::class, [
            'label' => $this->translator->trans("Token de sécurité", [], 'core_init'),
            'display' => 'floating',
            'bytes' => true,
            'attr' => ['group' => 'col-md-6'],
            'constraints' => [new Assert\NotBlank()]
        ]);

        $builder->add('token_security_back', Type\TextType::class, [
            'label' => $this->translator->trans("Token d'adminstration", [], 'core_init'),
            'display' => 'floating',
            'bytes' => true,
            'attr' => ['group' => 'col-md-6'],
            'constraints' => [new Assert\NotBlank()]
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