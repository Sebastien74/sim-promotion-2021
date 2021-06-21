<?php

namespace App\Form\Type\Security\Front;

use App\Entity\Core\Website;
use App\Form\Model\Security\Front\RegistrationFormModel;
use App\Twig\Content\CoreRuntime;
use App\Twig\Translation\i18nRuntime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * RegistrationType
 *
 * @property TranslatorInterface $translator
 * @property Request $request
 * @property CoreRuntime $coreRuntime
 * @property i18nRuntime $i18nRuntime
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class RegistrationType extends AbstractType
{
    private $translator;
    private $request;
    private $coreRuntime;
    private $i18nRuntime;

    /**
     * RegistrationType constructor.
     *
     * @param TranslatorInterface $translator
     * @param RequestStack $requestStack
     * @param CoreRuntime $coreRuntime
     * @param i18nRuntime $i18nRuntime
     */
    public function __construct(
        TranslatorInterface $translator,
        RequestStack $requestStack,
        CoreRuntime $coreRuntime,
        i18nRuntime $i18nRuntime)
    {
        $this->translator = $translator;
        $this->request = $requestStack->getCurrentRequest();
        $this->coreRuntime = $coreRuntime;
        $this->i18nRuntime = $i18nRuntime;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Website $website */
        $website = $options['website'];
        $mainPages = $this->coreRuntime->mainPages($website->getConfiguration());
        $legalNotice = !empty($mainPages['legale']) ? $this->request->getSchemeAndHttpHost() . '/' . $this->i18nRuntime->i18nUrl($website, $mainPages['legale']) : NULL;
        $cgv = !empty($mainPages['cgv']) ? $this->request->getSchemeAndHttpHost() . '/' . $this->i18nRuntime->i18nUrl($website, $mainPages['cgv']) : NULL;

        $builder->add('profile', ProfileRegistrationType::class);

        $builder->add('lastName', Type\TextType::class, [
            'label' => $this->translator->trans('Nom', [], 'security_cms'),
            'attr' => [
                'placeholder' => $this->translator->trans('Saisissez votre nom', [], 'security_cms'),
                'group' => 'col-lg-6'
            ]
        ]);

        $builder->add('firstName', Type\TextType::class, [
            'label' => $this->translator->trans('Prénom', [], 'security_cms'),
            'attr' => [
                'placeholder' => $this->translator->trans('Saisissez votre prénom', [], 'security_cms'),
                'group' => 'col-lg-6'
            ]
        ]);

        $builder->add('login', Type\TextType::class, [
            'label' => $this->translator->trans("Nom d'utilisateur", [], 'security_cms'),
            'attr' => [
                'placeholder' => $this->translator->trans('Saisissez un nom', [], 'security_cms'),
                'group' => 'col-lg-6'
            ]
        ]);

        $builder->add('email', Type\EmailType::class, [
            'label' => $this->translator->trans('E-mail', [], 'security_cms'),
            'attr' => [
                'placeholder' => $this->translator->trans('Saisissez un e-mail', [], 'security_cms'),
                'group' => 'col-lg-6'
            ]
        ]);

        $builder->add('plainPassword', Type\RepeatedType::class, [
            'label' => false,
            'type' => Type\PasswordType::class,
            'invalid_message' => $this->translator->trans('Les mots de passe sont différents', [], 'validators_cms'),
            'first_options' => [
                'label' => $this->translator->trans('Mot de passe', [], 'security_cms'),
                "attr" => [
                    'placeholder' => $this->translator->trans('Saisissez le mot de passe', [], 'security_cms'),
                    'group' => 'col-lg-6'
                ]
            ],
            'second_options' => [
                'label' => $this->translator->trans('Confirmation du mot de passe', [], 'security_cms'),
                "attr" => [
                    'placeholder' => $this->translator->trans('Saisissez le mot de passe', [], 'security_cms'),
                    'group' => 'col-lg-6'
                ]
            ],
        ]);

        $builder->add('agreeTerms', Type\CheckboxType::class, [
            'label' => $this->translator->trans("J’accepte les <a href='" . $cgv . "' target='_blank'>Conditions Générales de Vente</a> et les <a href='" . $legalNotice . "' target='_blank'>Conditions générales d'utilisation</a>", [], 'security_cms'),
            'help' => $this->translator->trans("Vous devez prendre connaissance des mentions légales et les cocher pour créer votre compte.", [], 'security_cms'),
            'attr' => [
                'group' => 'col-12 agree-terms-group'
            ]
        ]);

        $builder->add('locale', Type\HiddenType::class, [
            'data' => $this->request->getLocale()
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