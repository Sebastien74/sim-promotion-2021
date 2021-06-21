<?php

namespace App\Form\Type\Core\Init;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * LegalsType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class LegalsType extends AbstractType
{
    private $translator;

    /**
     * LegalsType constructor.
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
        $builder->add('legal_companyRepresentativeName', Type\TextType::class, [
            'required' => false,
            'display' => 'floating',
            'label' => $this->translator->trans("Nom du représentant légal de l’entreprise", [], 'admin'),
            'attr' => ['group' => 'col-md-4']
        ]);

        $builder->add('legal_capital', Type\TextType::class, [
            'required' => false,
            'display' => 'floating',
            'label' => $this->translator->trans("Capital", [], 'admin'),
            'attr' => ['group' => 'col-md-4']
        ]);

        $builder->add('legal_vatNumber', Type\TextType::class, [
            'required' => false,
            'display' => 'floating',
            'label' => $this->translator->trans("Numéro de TVA", [], 'admin'),
            'attr' => ['group' => 'col-md-4']
        ]);

        $builder->add('legal_siretNumber', Type\TextType::class, [
            'required' => false,
            'display' => 'floating',
            'label' => $this->translator->trans("Numéro de SIRET", [], 'admin'),
            'attr' => ['group' => 'col-md-4']
        ]);

        $builder->add('legal_commercialRegisterNumber', Type\TextType::class, [
            'required' => false,
            'display' => 'floating',
            'label' => $this->translator->trans("Numéro registre du commerce", [], 'admin'),
            'attr' => ['group' => 'col-md-4']
        ]);

        $builder->add('legal_companyAddress', Type\TextType::class, [
            'required' => false,
            'display' => 'floating',
            'label' => $this->translator->trans("Adresse", [], 'admin'),
            'attr' => ['group' => 'col-md-4']
        ]);

        $builder->add('legal_managerName', Type\TextType::class, [
            'required' => false,
            'display' => 'floating',
            'label' => $this->translator->trans("Nom du responsable de la publication", [], 'admin'),
            'attr' => ['group' => 'col-md-4']
        ]);

        $builder->add('legal_managerEmail', Type\EmailType::class, [
            'required' => false,
            'display' => 'floating',
            'label' => $this->translator->trans('E-mail du responsable', [], 'admin'),
            'attr' => ['group' => 'col-md-4'],
            'constraints' => [new Assert\Email()]
        ]);

        $builder->add('legal_webmasterName', Type\TextType::class, [
            'required' => false,
            'display' => 'floating',
            'data' => 'Agence Félix Création',
            'label' => $this->translator->trans("Nom du Webmaster", [], 'admin'),
            'attr' => ['group' => 'col-md-4']
        ]);

        $builder->add('legal_webmasterEmail', Type\EmailType::class, [
            'required' => false,
            'display' => 'floating',
            'data' => 'commercial@felix-creation.fr',
            'label' => $this->translator->trans('E-mail du Webmaster', [], 'admin'),
            'attr' => ['group' => 'col-md-4'],
            'constraints' => [new Assert\Email()]
        ]);

        $builder->add('legal_hostName', Type\TextType::class, [
            'required' => false,
            'display' => 'floating',
            'label' => $this->translator->trans("Nom de l'hébergeur", [], 'admin'),
            'attr' => ['group' => 'col-md-4']
        ]);

        $builder->add('legal_hostAddress', Type\TextType::class, [
            'required' => false,
            'display' => 'floating',
            'label' => $this->translator->trans("Adresse de l'hébergeur", [], 'admin'),
            'attr' => ['group' => 'col-md-4']
        ]);

        $builder->add('legal_protectionOfficerName', Type\TextType::class, [
            'required' => false,
            'display' => 'floating',
            'label' => $this->translator->trans("Nom du délégué à la protection des données", [], 'admin'),
            'attr' => ['group' => 'col-md-4']
        ]);

        $builder->add('legal_protectionOfficerEmail', Type\EmailType::class, [
            'required' => false,
            'display' => 'floating',
            'label' => $this->translator->trans("E-mail du délégué", [], 'admin'),
            'attr' => ['group' => 'col-md-4'],
            'constraints' => [new Assert\Email()]
        ]);

        $builder->add('legal_protectionOfficerAddress', Type\TextType::class, [
            'required' => false,
            'display' => 'floating',
            'label' => $this->translator->trans('Adresse du délégué', [], 'admin'),
            'attr' => ['group' => 'col-md-4']
        ]);

        $builder->add('legal_hostAddress', Type\TextType::class, [
            'required' => false,
            'display' => 'floating',
            'label' => $this->translator->trans("Adresse de l'hébergeur", [], 'admin'),
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