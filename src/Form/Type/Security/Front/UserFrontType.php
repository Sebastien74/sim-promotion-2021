<?php

namespace App\Form\Type\Security\Front;

use App\Entity\Security\UserFront;
use App\Form\Validator\UniqUserEmail;
use App\Form\Validator\UniqUserLogin;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * UserFrontType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class UserFrontType extends AbstractType
{
    private $translator;

    /**
     * UserFrontType constructor.
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
        $builder->add('lastName', Type\TextType::class, [
            'label' => $this->translator->trans("Nom", [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans("Saisissez un nom", [], 'admin'),
                'group' => 'col-md-6'
            ],
            'constraints' => [
                new Assert\NotBlank([
                    'message' => $this->translator->trans("Veuillez saisir votre nom.", [], 'admin')
                ])
            ]
        ]);

        $builder->add('firstName', Type\TextType::class, [
            'label' => $this->translator->trans("Prénom", [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans("Saisissez un prénom", [], 'admin'),
                'group' => 'col-md-6'
            ],
            'constraints' => [
                new Assert\NotBlank([
                    'message' => $this->translator->trans("Veuillez saisir votre prénom.", [], 'admin')
                ])
            ]
        ]);

        $builder->add('login', Type\TextType::class, [
            'label' => $this->translator->trans("Nom d'utilisateur", [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans("Saisissez un nom", [], 'admin'),
                'group' => 'col-md-6'
            ],
            'constraints' => [
                new UniqUserLogin(),
                new Assert\NotBlank([
                    'message' => $this->translator->trans("Veuillez saisir un identifiant.", [], 'admin')
                ])
            ]
        ]);

        $builder->add('email', Type\EmailType::class, [
            'label' => $this->translator->trans("E-mail", [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans("Saisissez un e-mail", [], 'admin'),
                'group' => 'col-md-6'
            ],
            'constraints' => [
                new Assert\NotBlank([
                    'message' => $this->translator->trans("Veuillez saisir un email.", [], 'admin')
                ]),
                new Assert\Email(),
                new UniqUserEmail()
            ]
        ]);

        $builder->add('profile', ProfileFrontType::class);

        $builder->add('file', Type\FileType::class, [
            'label' => false,
            'mapped' => false,
            'required' => false,
            'attr' => ['accept' => 'image/*', 'class' => 'dropify']
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserFront::class,
            'website' => NULL,
            'translation_domain' => 'front'
        ]);
    }
}