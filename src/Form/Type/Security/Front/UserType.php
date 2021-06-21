<?php

namespace App\Form\Type\Security\Front;

use App\Entity\Security\Company;
use App\Entity\Security\UserFront;
use App\Form\Widget as WidgetType;
use App\Repository\Security\CompanyRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * UserType
 *
 * @property TranslatorInterface $translator
 * @property bool $isInternalUser
 * @property bool $isUserManager
 * @property CompanyRepository $companyRepository
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class UserType extends AbstractType
{
    private $translator;
    private $isInternalUser;
    private $isUserManager;
    private $companyRepository;

    /**
     * UserType constructor.
     *
     * @param TranslatorInterface $translator
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param CompanyRepository $companyRepository
     */
    public function __construct(
        TranslatorInterface $translator,
        AuthorizationCheckerInterface $authorizationChecker,
        CompanyRepository $companyRepository)
    {
        $this->translator = $translator;
        $this->isInternalUser = $authorizationChecker->isGranted('ROLE_INTERNAL');
        $this->isUserManager = $authorizationChecker->isGranted('ROLE_USERS')
            || $authorizationChecker->isGranted('ROLE_INTERNAL');
        $this->companyRepository = $companyRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isNew = !$builder->getData()->getId();
        $haveCompanies = count($this->companyRepository->findAll()) > 0;

        $builder->add('login', Type\TextType::class, [
            'label' => $this->translator->trans("Nom d'utilisateur", [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans("Saisissez un nom d'utilisateur", [], 'admin'),
                'group' => $isNew && $haveCompanies ? 'col-md-3' : 'col-md-4'
            ],
            'constraints' => [new Assert\NotBlank()]
        ]);

        $builder->add('email', Type\EmailType::class, [
            'label' => $this->translator->trans("E-mail", [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans("Saisissez un e-mail", [], 'admin'),
                'group' => $isNew && $haveCompanies ? 'col-md-3' : 'col-md-4'
            ],
            'constraints' => [new Assert\Email()]
        ]);

        if (!$isNew) {

            $builder->add('lastName', Type\TextType::class, [
                'label' => $this->translator->trans("Nom de famille", [], 'admin'),
                'required' => false,
                'attr' => [
                    'placeholder' => $this->translator->trans("Saisissez un nom", [], 'admin'),
                    'group' => $isNew ? 'col-md-3' : 'col-md-4'
                ]
            ]);

            $builder->add('firstName', Type\TextType::class, [
                'label' => $this->translator->trans("Prénom", [], 'admin'),
                'required' => false,
                'attr' => [
                    'placeholder' => $this->translator->trans("Saisissez un prénom", [], 'admin'),
                    'group' => $isNew ? 'col-md-3' : 'col-md-4'
                ]
            ]);
        }

        $builder->add('locale', WidgetType\LanguageIconType::class, [
            'label' => $this->translator->trans("Langue", [], 'admin'),
            'attr' => ['group' => $isNew && $haveCompanies ? 'col-md-3' : 'col-md-4'],
            'constraints' => [new Assert\NotBlank()]
        ]);

        if ($haveCompanies) {
            $builder->add('company', EntityType::class, [
                'label' => $this->translator->trans("Entreprise", [], 'admin'),
                'required' => false,
                'class' => Company::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.name', 'ASC');
                },
                'choice_label' => function ($entity) {
                    return strip_tags($entity->getName());
                },
                'display' => "search",
                "attr" => [
                    'placeholder' => $this->translator->trans('Séléctionnez', [], 'admin'),
                    'group' => $isNew ? "col-md-3" : "col-md-4"
                ]
            ]);
        }

        if ($isNew) {

            $builder->add('plainPassword', Type\RepeatedType::class, [
                'label' => false,
                'type' => Type\PasswordType::class,
                'invalid_message' => $this->translator->trans('Les mots de passe sont différents', [], 'admin'),
                'first_options' => [
                    'label' => $this->translator->trans('Mot de passe', [], 'admin'),
                    "attr" => [
                        'placeholder' => $this->translator->trans('Saisissez le mot de passe', [], 'admin'),
                        'group' => "col-md-6 password-generator"
                    ],
                    'constraints' => [new Assert\NotBlank()]
                ],
                'second_options' => [
                    'label' => $this->translator->trans('Confirmation du mot de passe', [], 'admin'),
                    "attr" => [
                        'placeholder' => $this->translator->trans('Saisissez le mot de passe', [], 'admin'),
                        'group' => "col-md-6"
                    ],
                    'constraints' => [new Assert\NotBlank()]
                ],
            ]);
        } else {

            $builder->add('active', Type\CheckboxType::class, [
                'label' => $this->translator->trans("Activer le compte ?", [], 'admin'),
                'attr' => ['group' => $haveCompanies ? 'col-md-4' : 'col-md-4 my-md-auto']
            ]);

            $builder->add('file', Type\FileType::class, [
                'label' => false,
                'mapped' => false,
                'required' => false,
                'attr' => ['accept' => 'image/*', 'class' => 'dropify']
            ]);
        }

        $save = new WidgetType\SubmitType($this->translator);
        $save->add($builder);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserFront::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}