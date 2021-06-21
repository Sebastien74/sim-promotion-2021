<?php

namespace App\Form\Type\Security\Admin;

use App\Form\Model\Security\Admin\RegistrationFormModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * RegistrationType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class RegistrationType extends AbstractType
{
    private $translator;

    /**
     * RegistrationType constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('login', Type\TextType::class, [
            'label' => $this->translator->trans('Identifiant', [], 'security_cms'),
            'attr' => [
                'placeholder' => $this->translator->trans('Saisissez un identifiant', [], 'security_cms')
            ]
        ]);

        $builder->add('email', Type\EmailType::class, [
            'label' => $this->translator->trans('E-mail', [], 'security_cms'),
            'attr' => [
                'placeholder' => $this->translator->trans('Saisissez un e-mail', [], 'security_cms')
            ]
        ]);

        $builder->add('lastName', Type\EmailType::class, [
            'label' => $this->translator->trans('Nom', [], 'security_cms'),
            'attr' => [
                'placeholder' => $this->translator->trans('Saisissez votre nom', [], 'security_cms')
            ]
        ]);

        $builder->add('firstName', Type\EmailType::class, [
            'label' => $this->translator->trans('Prénom', [], 'security_cms'),
            'attr' => [
                'placeholder' => $this->translator->trans('Saisissez votre prénom', [], 'security_cms')
            ]
        ]);

        $builder->add('plainPassword', Type\RepeatedType::class, [
            'label' => false,
            'type' => Type\PasswordType::class,
            'invalid_message' => $this->translator->trans('Les mots de passe sont différents', [], 'validators_cms'),
            'first_options' => [
                'label' => $this->translator->trans('Mot de passe', [], 'security_cms'),
                "attr" => [
                    'placeholder' => $this->translator->trans('Saisissez le mot de passe', [], 'security_cms')
                ]
            ],
            'second_options' => [
                'attr' => [
                    'group_class' => "p-0"
                ],
                'label' => $this->translator->trans('Confirmation du mot de passe', [], 'security_cms'),
                "attr" => [
                    'placeholder' => $this->translator->trans('Saisissez le mot de passe', [], 'security_cms')
                ]
            ],
        ]);

        $builder->add('agreeTerms', Type\CheckboxType::class, [
            'label' => $this->translator->trans('Conditions générales', [], 'security_cms')
        ]);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RegistrationFormModel::class,
            'website' => NULL
        ]);
    }
}