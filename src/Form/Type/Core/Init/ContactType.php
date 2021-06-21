<?php

namespace App\Form\Type\Core\Init;

use App\Form\Validator as Validator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * ContactType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ContactType extends AbstractType
{
    private $translator;

    /**
     * ContactType constructor.
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
        $builder->add('phone_number', Type\TelType::class, [
            'label' => $this->translator->trans("Numéro de téléphone", [], 'core_init'),
            'required' => false,
            'display' => 'floating',
            'attr' => ['group' => 'col-md-6'],
            'constraints' => [new Validator\Phone()]
        ]);

        $builder->add('phone_tag_number', Type\TelType::class, [
            'label' => $this->translator->trans("Numéro de téléphone (href)", [], 'core_init'),
            'required' => false,
            'display' => 'floating',
            'attr' => ['group' => 'col-md-6'],
            'constraints' => [new Validator\Phone()]
        ]);

        $builder->add('contact_email', Type\EmailType::class, [
            'label' => $this->translator->trans("Email de contact", [], 'core_init'),
            'display' => 'floating',
            'attr' => ['group' => 'col-md-6'],
            'constraints' => [new Assert\NotBlank(), new Assert\Email()]
        ]);

        $builder->add('no_reply_email', Type\EmailType::class, [
            'label' => $this->translator->trans("Email no-reply", [], 'core_init'),
            'display' => 'floating',
            'attr' => ['group' => 'col-md-6'],
            'constraints' => [new Assert\NotBlank(), new Assert\Email()]
        ]);

        $builder->add('address_address', Type\TextType::class, [
            'label' => $this->translator->trans("Adresse", [], 'core_init'),
            'display' => 'floating',
            'constraints' => [new Assert\NotBlank()]
        ]);

        $builder->add('zip_code_address', Type\TextType::class, [
            'label' => $this->translator->trans("Code postal", [], 'core_init'),
            'display' => 'floating',
            'attr' => ['group' => 'col-md-4'],
            'constraints' => [new Validator\ZipCode(), new Assert\NotBlank()]
        ]);

        $builder->add('city_address', Type\TextType::class, [
            'label' => $this->translator->trans("Ville", [], 'core_init'),
            'display' => 'floating',
            'attr' => ['group' => 'col-md-4'],
            'constraints' => [new Assert\NotBlank()]
        ]);

        $builder->add('department_address', Type\TextType::class, [
            'label' => $this->translator->trans("Département", [], 'core_init'),
            'required' => false,
            'display' => 'floating',
            'attr' => ['group' => 'col-md-4']
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