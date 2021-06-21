<?php

namespace App\Form\Type\Module\Form;

use App\Entity\Module\Form\Configuration;
use App\Entity\Core\Module;
use App\Entity\Layout\Page;
use App\Form\Widget as WidgetType;
use Doctrine\ORM\EntityManagerInterface;
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
 * ConfigurationType
 *
 * @property TranslatorInterface $translator
 * @property EntityManagerInterface $entityManager
 * @property AuthorizationCheckerInterface $authorizationChecker
 * @property bool $isInternalUser
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ConfigurationType extends AbstractType
{
    private $translator;
    private $entityManager;
    private $authorizationChecker;
    private $isInternalUser;

    /**
     * ConfigurationType constructor.
     *
     * @param TranslatorInterface $translator
     * @param EntityManagerInterface $entityManager
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(TranslatorInterface $translator, EntityManagerInterface $entityManager, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->authorizationChecker = $authorizationChecker;
        $this->isInternalUser = $authorizationChecker->isGranted('ROLE_INTERNAL');
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Configuration $configuration */
        $configuration = !empty($options['entity']) ? $options['entity'] : NULL;

        $builder->add('receivingEmails', WidgetType\TagInputType::class, [
            'label' => $this->translator->trans("E-mails de réception", [], 'admin'),
            'required' => false,
            'attr' => [
                'group' => 'col-md-8',
                'placeholder' => $this->translator->trans('Ajouter des e-mails', [], 'admin')
            ]
        ]);

        $builder->add('sendingEmail', Type\EmailType::class, [
            'label' => $this->translator->trans("E-mail d'envoi", [], 'admin'),
            'attr' => [
                'group' => 'col-md-4',
                'placeholder' => $this->translator->trans('Saisissez un e-mail', [], 'admin')
            ],
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\Email()
            ]
        ]);

        if ($this->isInternalUser) {

            $builder->add('maxShipments', Type\IntegerType::class, [
                'label' => $this->translator->trans("Maximum de soumissions", [], 'admin'),
                'required' => false,
                'attr' => [
                    'placeholder' => $this->translator->trans('Saisissez un chiffre', [], 'admin'),
                    'group' => 'col-md-2',
                    'data-config' => true
                ]
            ]);

            $builder->add('pageRedirection', EntityType::class, [
                'required' => false,
                'label' => $this->translator->trans('Page de redirection', [], 'admin'),
                'attr' => [
                    'data-placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
                    'group' => 'col-md-2 allow-clear'
                ],
                'class' => Page::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->leftJoin('p.urls', 'u')
                        ->andWhere('p.deletable = :deletable')
                        ->andWhere('u.isOnline = :isOnline')
                        ->setParameter('deletable', true)
                        ->setParameter('isOnline', true)
                        ->addSelect('u')
                        ->orderBy('p.adminName', 'ASC');
                },
                'choice_label' => function ($entity) {
                    return strip_tags($entity->getAdminName());
                },
                'display' => 'search'
            ]);

            $dates = new WidgetType\PublicationDatesType($this->translator);
            $dates->add($builder, [
                'entity' => $configuration,
                'startLabel' => $this->translator->trans('Afficher à partir du', [], 'admin'),
                'endLabel' => $this->translator->trans('Retirer à partir du', [], 'admin')
            ]);

            $builder->add('dbRegistration', Type\CheckboxType::class, [
                'required' => false,
                'display' => 'button',
                'color' => 'outline-dark',
                'label' => $this->translator->trans("Enregistrer les contacts", [], 'admin'),
                'attr' => ['group' => 'col-md-3', 'class' => 'w-100', 'data-config' => true]
            ]);

            $builder->add('attachmentsInMail', Type\CheckboxType::class, [
                'required' => false,
                'display' => 'button',
                'color' => 'outline-dark',
                'label' => $this->translator->trans("Fichiers en pièces-jointes du mail", [], 'admin'),
                'attr' => ['group' => 'col-md-3', 'class' => 'w-100', 'data-config' => true]
            ]);

            $builder->add('uniqueContact', Type\CheckboxType::class, [
                'required' => false,
                'display' => 'button',
                'color' => 'outline-dark',
                'label' => $this->translator->trans("Un seul envoi de mail possible", [], 'admin'),
                'attr' => ['group' => 'col-md-3', 'class' => 'w-100', 'data-config' => true]
            ]);

            $builder->add('thanksModal', Type\CheckboxType::class, [
                'required' => false,
                'display' => 'button',
                'color' => 'outline-dark',
                'label' => $this->translator->trans("Afficher modal de remerciement", [], 'admin'),
                'attr' => ['group' => 'col-md-3', 'class' => 'w-100', 'data-config' => true]
            ]);

        }

        $builder->add('confirmEmail', Type\CheckboxType::class, [
            'required' => false,
            'display' => 'button',
            'color' => 'outline-dark',
            'label' => $this->translator->trans("Envoyer un e-mail de confirmation", [], 'admin'),
            'attr' => ['group' => 'col-md-3', 'class' => 'w-100', 'data-config' => true]
        ]);

        if ($this->isInternalUser) {

            $builder->add('recaptcha', Type\CheckboxType::class, [
                'required' => false,
                'display' => 'button',
                'color' => 'outline-dark',
                'label' => $this->translator->trans("Activer le recaptcha", [], 'admin'),
                'attr' => ['group' => 'col-md-3', 'class' => 'w-100', 'data-config' => true]
            ]);

            $module = $this->entityManager->getRepository(Module::class)->findOneBy(['slug' => 'form-calendar']);
            $moduleActive = $this->entityManager->getRepository(\App\Entity\Core\Configuration::class)->moduleExist($options['website'], $module);
            if ($moduleActive && $this->authorizationChecker->isGranted('ROLE_FORM_CALENDAR')) {
                $builder->add('calendarsActive', Type\CheckboxType::class, [
                    'required' => false,
                    'display' => 'button',
                    'color' => 'outline-dark',
                    'label' => $this->translator->trans("Activer les calendriers", [], 'admin'),
                    'attr' => ['group' => 'col-md-3', 'class' => 'w-100', 'data-config' => true]
                ]);
            }

            if (!$options['excludes'] || !array('ajax', $options['excludes'])) {
                $builder->add('ajax', Type\CheckboxType::class, [
                    'required' => false,
                    'display' => 'button',
                    'color' => 'outline-dark',
                    'label' => $this->translator->trans("Soumission en ajax", [], 'admin'),
                    'attr' => ['group' => 'col-md-3', 'class' => 'w-100', 'data-config' => true]
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
            'data_class' => Configuration::class,
            'website' => NULL,
            'is_new' => false,
            'entity' => NULL,
            'excludes' => [],
            'translation_domain' => 'admin'
        ]);
    }
}