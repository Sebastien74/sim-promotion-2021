<?php

namespace App\Form\Type\Gdpr;

use App\Entity\Gdpr\Cookie;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CookieType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CookieType extends AbstractType
{
    private $translator;

    /**
     * CategoryType constructor.
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
        $builder->add('code', TextType::class, [
            'label' => $this->translator->trans("Code du Cookie", [], 'admin'),
            'required' => false,
            'attr' => [
                'placeholder' => $this->translator->trans('Saisissez le code', [], 'admin')
            ]
        ]);

        $save = new WidgetType\SubmitType($this->translator);
        $save->add($builder);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Cookie::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}