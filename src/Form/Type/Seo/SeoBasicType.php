<?php

namespace App\Form\Type\Seo;

use App\Entity\Seo\Seo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * SeoBasicType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SeoBasicType extends AbstractType
{
    private $translator;

    /**
     * SeoType constructor.
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
        $builder->add('metaTitle', Type\TextType::class, [
            'label' => $this->translator->trans('Méta titre', [], 'admin'),
            'counter' => 72,
            'attr' => [
                'placeholder' => $this->translator->trans("Saisissez un titre", [], 'admin'),
                'class' => "meta-title refer-code"
            ],
            'required' => false
        ]);

        $builder->add('metaDescription', Type\TextareaType::class, [
            'label' => $this->translator->trans('Méta description', [], 'admin'),
            'counter' => 155,
            'editor' => false,
            'attr' => [
                'placeholder' => $this->translator->trans("Éditez une description", [], 'admin'),
                'class' => "meta-description"
            ],
            'required' => false
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Seo::class,
            'website' => NULL,
            'have_index_page' => false,
            'translation_domain' => 'admin'
        ]);
    }
}