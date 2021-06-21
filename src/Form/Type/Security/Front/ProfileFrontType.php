<?php

namespace App\Form\Type\Security\Front;

use App\Entity\Security\Profile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ProfileFrontType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ProfileFrontType extends AbstractType
{
    private $translator;

    /**
     * ProfileFrontType constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('gender', Type\ChoiceType::class, [
            'label' => false,
            'expanded' => true,
            'choices' => [
                $this->translator->trans('M.', [], 'security_cms') => 'mr',
                $this->translator->trans('Mme', [], 'security_cms') => 'ms',
            ],
            'attr' => ['group' => 'col-12'],
            'constraints' => [
                new Assert\NotBlank([
                    'message' => $this->translator->trans("Veuillez séléctionnez un genre.", [], 'admin')
                ])
            ]
        ]);

        if ($_ENV['SECURITY_FRONT_ADDRESSES']) {
            $builder->add('addresses', CollectionType::class, [
                'label' => false,
                'entry_type' => AddressFrontType::class,
                'entry_options' => ['website' => $options['website']]
            ]);
        }
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Profile::class,
            'website' => NULL
        ]);
    }
}