<?php

namespace App\Form\Type\Security\Front;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * PasswordRequestType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class PasswordRequestType extends AbstractType
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
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', Type\EmailType::class, [
            'label' => false,
            'attr' => [
                'placeholder' => $this->translator->trans("Saisissez votre e-mail", [], 'security_cms')
            ],
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\Email()
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => NULL,
            'website' => NULL
        ]);
    }
}