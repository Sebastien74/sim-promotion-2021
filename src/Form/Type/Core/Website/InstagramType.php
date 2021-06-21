<?php

namespace App\Form\Type\Core\Website;

use App\Entity\Api\Instagram;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * InstagramType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class InstagramType extends AbstractType
{
    private $translator;

    /**
     * GoogleType constructor.
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
        $builder->add('accessToken', Type\TextType::class, [
            'required' => false,
            'label' => $this->translator->trans('API token', [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans('Saisissez le token', [], 'admin'),
                'group' => "col-md-8"
            ]
        ]);

        $builder->add('nbrItems', Type\IntegerType::class, [
            'required' => false,
            'label' => $this->translator->trans('Nombre de posts', [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans('Saisissez un chiffre', [], 'admin'),
                'group' => "col-md-4"
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Instagram::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}