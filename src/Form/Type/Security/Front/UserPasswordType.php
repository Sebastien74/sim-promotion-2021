<?php

namespace App\Form\Type\Security\Front;

use App\Entity\Security\UserFront;
use App\Form\Widget\SubmitType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * UserPasswordType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class UserPasswordType extends AbstractType
{
    private $translator;

    /**
     * UserPasswordType constructor.
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
        $builder->add('plainPassword', Type\RepeatedType::class, [
            'label' => false,
            'type' => Type\PasswordType::class,
            'invalid_message' => $this->translator->trans('Les mots de passe sont différents', [], 'validators_cms'),
            'constraints' => [new NotBlank()],
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

        $save = new SubmitType($this->translator);
        $save->add($builder, [
            'only_save' => true,
            'has_ajax' => true
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
        ]);
    }
}