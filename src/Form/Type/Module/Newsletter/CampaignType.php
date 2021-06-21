<?php

namespace App\Form\Type\Module\Newsletter;

use App\Entity\Module\Newsletter\Campaign;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CampaignType
 *
 * @property TranslatorInterface $translator
 * @property bool $isInternalUser
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CampaignType extends AbstractType
{
    private $translator;
    private $isInternalUser;

    /**
     * CampaignType constructor.
     *
     * @param TranslatorInterface $translator
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(TranslatorInterface $translator, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->translator = $translator;
        $this->isInternalUser = $authorizationChecker->isGranted('ROLE_INTERNAL');
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isNew = !$builder->getData()->getId();

        $adminName = new WidgetType\AdminNameType($this->translator);
        $adminName->add($builder, ['slug-internal' => $this->isInternalUser]);

        if (!$isNew && !$this->isInternalUser) {
            $i18ns = new WidgetType\i18nsCollectionType($this->translator);
            $i18ns->add($builder, [
                'website' => $options['website'],
                'data-config' => true,
                'fields' => ['introduction'],
                'label_fields' => [
                    'introduction' => $this->translator->trans('Info RGPD', [], 'admin')
                ],
            ]);
        }

        if (!$isNew && $this->isInternalUser) {

            $i18ns = new WidgetType\i18nsCollectionType($this->translator);
            $i18ns->add($builder, [
                'website' => $options['website'],
                'data_config' => true,
                'content_config' => false,
                'fields' => ['introduction', 'body', 'placeholder' => 'col-12'],
                'label_fields' => [
                    'body' => $this->translator->trans("Corps de l'e-mail de remerciement", [], 'admin'),
                    'introduction' => $this->translator->trans('Info RGPD', [], 'admin'),
                    'placeholder' => $this->translator->trans('Message popup de remerciement', [], 'admin'),
                    'introductionAlignment' => $this->translator->trans("Alignement de l'info", [], 'admin')
                ],
            ]);

            $builder->add('externalFormAction', Type\TextType::class, [
                'required' => false,
                'label' => $this->translator->trans('Action du formulaire Mailchimp', [], 'admin'),
                'attr' => [
                    'placeholder' => $this->translator->trans("Saisissez l'action", [], 'admin'),
                    'group' => 'col-md-6'
                ]
            ]);

            $builder->add('externalFieldEmail', Type\TextType::class, [
                'required' => false,
                'label' => $this->translator->trans('Nom du champs de mail Mailchimp', [], 'admin'),
                'attr' => [
                    'placeholder' => $this->translator->trans('Saisissez le nom', [], 'admin'),
                    'group' => 'col-md-3'
                ]
            ]);

            $builder->add('externalFormToken', Type\TextType::class, [
                'required' => false,
                'label' => $this->translator->trans('Nom du champs token Mailchimp', [], 'admin'),
                'attr' => [
                    'placeholder' => $this->translator->trans('Saisissez le token', [], 'admin'),
                    'group' => 'col-md-3'
                ]
            ]);

            $builder->add('internalRegistration', Type\CheckboxType::class, [
                'required' => false,
                'display' => 'button',
                'color' => 'outline-dark',
                'label' => $this->translator->trans("Activer l'enregistrement interne", [], 'admin'),
                'attr' => ['group' => 'col-md-3', 'class' => 'w-100', 'data-config' => true]
            ]);

            $builder->add('thanksModal', Type\CheckboxType::class, [
                'required' => false,
                'display' => 'button',
                'color' => 'outline-dark',
                'label' => $this->translator->trans("Afficher modal de remerciement", [], 'admin'),
                'attr' => ['group' => 'col-md-3', 'class' => 'w-100', 'data-config' => true]
            ]);

            $builder->add('emailConfirmation', Type\CheckboxType::class, [
                'required' => false,
                'display' => 'button',
                'color' => 'outline-dark',
                'label' => $this->translator->trans("Envoyer un e-mail de confirmation", [], 'admin'),
                'attr' => ['group' => 'col-md-3', 'class' => 'w-100', 'data-config' => true]
            ]);

            $builder->add('recaptcha', Type\CheckboxType::class, [
                'required' => false,
                'display' => 'button',
                'color' => 'outline-dark',
                'label' => $this->translator->trans("Activer le recaptcha", [], 'admin'),
                'attr' => ['group' => 'col-md-3', 'class' => 'w-100', 'data-config' => true]
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
            'data_class' => Campaign::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}