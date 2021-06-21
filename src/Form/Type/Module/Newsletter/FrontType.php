<?php

namespace App\Form\Type\Module\Newsletter;

use App\Entity\Module\Newsletter\Campaign;
use App\Entity\Module\Newsletter\Email;
use App\Form\Widget as WidgetType;
use App\Form\Validator\UniqEmailCampaign;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * FrontType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FrontType extends AbstractType
{
    private $translator;

    /**
     * FrontType constructor.
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
        /** @var Campaign $campaign */
        $campaign = $options['form_data'];

        $recaptcha = new WidgetType\RecaptchaType($this->translator);
        $recaptcha->add($builder, $options['form_data']);

        $constraints = $campaign->getInternalRegistration() ? [
            new Assert\NotBlank([
                "message" => $this->translator->trans("Vous devez renseigner votre e-mail.", [], 'front_form')
            ]),
            new Assert\Email(),
            new UniqEmailCampaign()
        ] : [
            new Assert\NotBlank([
                "message" => $this->translator->trans("Vous devez renseigner votre e-mail.", [], 'front_form')
            ]),
            new Assert\Email()
        ];

        $builder->add('email', Type\EmailType::class, [
            'label' => false,
            'attr' => [
                'placeholder' => $this->translator->trans("Votre e-mail", [], 'front_form'),
                'class' => "text-center text-md-center text-lg-start newsletter-form-email",
                'autocomplete' => 'off'
            ],
            'constraints' => $constraints
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Email::class,
            'form_data' => NULL,
            'translation_domain' => 'front_form'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return "front_newsletter";
    }
}