<?php

namespace App\Form\Type\Security\Admin;

use App\Entity\Core\Website;
use App\Entity\Security\Company;
use App\Entity\Security\Group;
use App\Entity\Security\User;
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
 * @property CompanyRepository $companyRepository
 * @property bool $isInternalUser
 * @property bool $isUserManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class UserType extends AbstractType
{
    private $translator;
    private $companyRepository;
    private $isInternalUser;
    private $isUserManager;

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
                'group' => $isNew ? 'col-md-3' : 'col-md-4'
            ]
        ]);

        $builder->add('email', Type\EmailType::class, [
            'label' => $this->translator->trans("E-mail", [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans("Saisissez un e-mail", [], 'admin'),
                'group' => $isNew ? 'col-md-3' : 'col-md-4'
            ],
            'constraints' => [new Assert\Email()]
        ]);

        if (!$isNew) {

            $builder->add('lastName', Type\TextType::class, [
                'label' => $this->translator->trans("Nom de famille", [], 'admin'),
                'required' => false,
                'attr' => [
                    'placeholder' => $this->translator->trans("Saisissez un nom", [], 'admin'),
                    'group' => 'col-md-4'
                ]
            ]);

            $builder->add('firstName', Type\TextType::class, [
                'label' => $this->translator->trans("Prénom", [], 'admin'),
                'required' => false,
                'attr' => [
                    'placeholder' => $this->translator->trans("Saisissez un prénom", [], 'admin'),
                    'group' => 'col-md-4'
                ]
            ]);
        }

        if ($this->isUserManager) {
            $builder->add('group', EntityType::class, [
                'label' => $this->translator->trans("Groupe", [], 'admin'),
                'class' => Group::class,
                'display' => 'search',
                'query_builder' => function (EntityRepository $er) {
                    if (!$this->isInternalUser) {
                        return $er->createQueryBuilder('g')
                            ->andWhere('g.slug != :slug')
                            ->setParameter('slug', 'internal')
                            ->orderBy('g.adminName', 'ASC');
                    }
                },
                'choice_label' => function ($entity) {
                    return strip_tags($entity->getAdminName());
                },
                'placeholder' => $this->translator->trans("Sélectionnez", [], 'admin'),
                'attr' => ['group' => $isNew ? 'col-md-3' : 'col-md-4'],
                'constraints' => [new Assert\NotBlank()]
            ]);
        }

        $builder->add('locale', WidgetType\LanguageIconType::class, [
            'label' => $this->translator->trans("Langue", [], 'admin'),
            'attr' => ['group' => $isNew ? 'col-md-3' : 'col-md-4'],
            'constraints' => [new Assert\NotBlank()]
        ]);

        $class = $isNew ? 'col-12' : "col-12";
        $class = $isNew && $haveCompanies ? 'col-md-6' : $class;
        $builder->add('websites', EntityType::class, [
            'label' => 'Site(s)',
            'required' => true,
            'class' => Website::class,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('c')
                    ->orderBy('c.adminName', 'ASC');
            },
            'choice_label' => function ($entity) {
                return strip_tags($entity->getAdminName());
            },
            'multiple' => true,
            'display' => "search",
            "attr" => [
                'data-placeholder' => $this->translator->trans('Séléctionnez', [], 'security_cms'),
                'group' => $class
            ],
            'constraints' => [new Assert\Count([
                'min' => 1,
                'minMessage' => $this->translator->trans('Vous devez sélctionner au moins un site.', [], 'security_cms')
            ])]
        ]);

        if ($haveCompanies) {

            $builder->add('companies', EntityType::class, [
                'label' => 'Entreprise(s)',
                'required' => false,
                'class' => Company::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.name', 'ASC');
                },
                'choice_label' => function ($entity) {
                    return strip_tags($entity->getName());
                },
                'multiple' => true,
                'display' => "search",
                "attr" => [
                    'placeholder' => $this->translator->trans('Séléctionnez', [], 'security_cms'),
                    'group' => $isNew ? 'col-md-6' : "col-12"
                ]
            ]);
        }

        if ($isNew) {

            $builder->add('plainPassword', Type\RepeatedType::class, [
                'label' => false,
                'type' => Type\PasswordType::class,
                'invalid_message' => $this->translator->trans('Les mots de passe sont différents', [], 'validators_cms'),
                'first_options' => [
                    'label' => $this->translator->trans('Mot de passe', [], 'security_cms'),
                    "attr" => [
                        'placeholder' => $this->translator->trans('Saisissez le mot de passe', [], 'security_cms'),
                        'group' => "col-md-6 password-generator"
                    ]
                ],
                'second_options' => [
                    'label' => $this->translator->trans('Confirmation du mot de passe', [], 'security_cms'),
                    "attr" => [
                        'placeholder' => $this->translator->trans('Saisissez le mot de passe', [], 'security_cms'),
                        'group' => "col-md-6"
                    ]
                ],
            ]);
        } else {

            $builder->add('active', Type\CheckboxType::class, [
                'required' => false,
                'display' => 'button',
                'color' => 'outline-dark',
                'label' => $this->translator->trans("Activer le compte", [], 'admin'),
                'attr' => ['group' => 'col-md-3 d-flex align-items-end', 'class' => 'w-100']
            ]);

            $builder->add('file', Type\FileType::class, [
                'label' => false,
                'mapped' => false,
                'required' => false,
                'attr' => ['accept' => 'image/*', 'class' => 'dropify']
            ]);
        }

        if(!$isNew && $this->isInternalUser) {
            $builder->add('theme', WidgetType\AdminThemeType::class, [
                'label' => $this->translator->trans('Thème', [], 'admin'),
                'attr' => ['group' => 'col-md-2']
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
            'data_class' => User::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}