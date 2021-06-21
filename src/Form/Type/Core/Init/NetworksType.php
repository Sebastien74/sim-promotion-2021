<?php

namespace App\Form\Type\Core\Init;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * NetworksType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class NetworksType extends AbstractType
{
    private $translator;

    /**
     * NetworksType constructor.
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
        $this->getNetworks($builder);

        $builder->add('save', Type\SubmitType::class, [
            'label' => $this->translator->trans("Enregistrer", [], 'core_init'),
            'attr' => ['class' => 'btn-primary mt-4']
        ]);
    }

    /**
     * Get Networks
     *
     * @param FormBuilderInterface $builder
     */
    private function getNetworks(FormBuilderInterface $builder)
    {
        $socialNetworks = ['twitter', 'facebook', 'google', 'youtube', 'instagram', 'linkedin', 'pinterest', 'tripadvisor'];
        foreach ($socialNetworks as $socialNetwork) {
            $builder->add('network_' . $socialNetwork, Type\UrlType::class, [
                'required' => false,
                'display' => 'floating',
                'label' => ucfirst($socialNetwork),
                'attr' => ['group' => 'col-md-6'],
                'constraints' => [new Assert\Url()]
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => NULL,
            'translation_domain' => 'admin'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return "project_init";
    }
}