<?php

namespace App\Form\Type\Security\Front;

use App\Form\Model\Security\Front\ProfileRegistrationModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * ProfileRegistrationType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ProfileRegistrationType extends AbstractType
{
    private $translator;

    /**
     * ProfileRegistrationType constructor.
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
            'attr' => ['group' => 'col-12']
        ]);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProfileRegistrationModel::class,
            'website' => NULL
        ]);
    }
}