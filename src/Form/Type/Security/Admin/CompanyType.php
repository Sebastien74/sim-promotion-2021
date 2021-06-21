<?php

namespace App\Form\Type\Security\Admin;

use App\Entity\Security\Company;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CompanyType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CompanyType extends AbstractType
{
    private $translator;

    /**
     * FaqType constructor.
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
        $isNew = !$builder->getData()->getId();

        $builder->add('name', Type\TextType::class, [
            'label' => $this->translator->trans("Nom de l'entreprise", [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans("Saisissez un nom", [], 'admin'),
                'group' => $isNew ? 'col-12' : 'col-md-9'
            ],
            'constraints' => [new Assert\NotBlank()]
        ]);

        if (!$isNew) {

            $builder->add('locale', WidgetType\LanguageIconType::class, [
                'required' => false,
                'label' => $this->translator->trans("Langue", [], 'admin'),
                'attr' => ['group' => 'col-md-3']
            ]);

            $builder->add('email', Type\EmailType::class, [
                'required' => false,
                'label' => $this->translator->trans("E-mail", [], 'admin'),
                'attr' => [
                    'placeholder' => $this->translator->trans("Saisissez un e-mail", [], 'admin'),
                    'group' => 'col-md-4'
                ]
            ]);

            $builder->add('contactLastName', Type\EmailType::class, [
                'required' => false,
                'label' => $this->translator->trans("Nom du contact principal", [], 'admin'),
                'attr' => [
                    'placeholder' => $this->translator->trans("Saisissez un nom", [], 'admin'),
                    'group' => 'col-md-4'
                ]
            ]);

            $builder->add('contactFirstName', Type\EmailType::class, [
                'required' => false,
                'label' => $this->translator->trans("Prénom du contact principal", [], 'admin'),
                'attr' => [
                    'placeholder' => $this->translator->trans("Saisissez un e-mail", [], 'admin'),
                    'group' => 'col-md-4'
                ]
            ]);

            $builder->add('file', Type\FileType::class, [
                'label' => false,
                'mapped' => false,
                'required' => false,
                'attr' => ['accept' => 'image/*', 'class' => 'dropify']
            ]);

            $builder->add('address', CompanyAddressType::class, ['label' => false]);
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
            'data_class' => Company::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}