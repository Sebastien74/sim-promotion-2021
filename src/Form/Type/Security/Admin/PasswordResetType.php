<?php

namespace App\Form\Type\Security\Admin;

use App\Form\Model\Security\Admin\PasswordResetModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * PasswordResetType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class PasswordResetType extends AbstractType
{
    private $translator;

    /**
     * PasswordResetType constructor.
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
            'first_options' => [
                'label' => $this->translator->trans('Mot de passe', [], 'security_cms'),
                "attr" => [
                    'placeholder' => $this->translator->trans('Saisissez le mot de passe', [], 'security_cms'),
                    'class' => 'pt-2 pb-2'
                ]
            ],
            'second_options' => [
                'label' => $this->translator->trans('Confirmation du mot de passe', [], 'security_cms'),
                "attr" => [
                    'placeholder' => $this->translator->trans('Saisissez le mot de passe', [], 'security_cms'),
                    'class' => 'pt-2 pb-2'
                ]
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PasswordResetModel::class,
            'website' => NULL,
        ]);
    }
}