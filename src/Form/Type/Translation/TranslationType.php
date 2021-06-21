<?php

namespace App\Form\Type\Translation;

use App\Entity\Translation\Translation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * TranslationType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TranslationType extends AbstractType
{
    private $translator;

    /**
     * TranslationType constructor.
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
        $builder->add('content', Type\TextType::class, [
            'required' => false,
            'attr' => [
                'placeholder' => $this->translator->trans('Saisissez un contenu', [], 'admin')
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Translation::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}