<?php

namespace App\Form\Type\Information;

namespace App\Form\Type\Information;

use App\Entity\Information\Email;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * AddressEmailType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class AddressEmailType extends AbstractType
{
    private $translator;

    /**
     * AddressEmailType constructor.
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
            'label' => $this->translator->trans('E-mail', [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans('Saisissez un e-mail', [], 'admin')
            ],
            'constraints' => [new Assert\NotBlank()]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Email::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}