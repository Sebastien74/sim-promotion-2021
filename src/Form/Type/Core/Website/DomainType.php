<?php

namespace App\Form\Type\Core\Website;

use App\Entity\Core\Configuration;
use App\Entity\Core\Domain;
use App\Entity\Core\Website;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Languages;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * DomainType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class DomainType extends AbstractType
{
    private $translator;

    /**
     * DomainType constructor.
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
        $website = $options['website_edit'] instanceof Website ? $options['website_edit'] : $options['website'];
        $configuration = $website->getConfiguration();
        $locales = $this->getLocales($configuration);

        if (!empty($locales) && count($locales) > 1) {

            $builder->add('locale', Type\ChoiceType::class, [
                'label' => false,
                'placeholder' => $this->translator->trans("Sélectionnez", [], 'admin'),
                'choices' => $locales,
                'choice_translation_domain' => false,
                'attr' => [
                    'class' => 'select-icons',
                    'group' => 'col-12'
                ],
                'choice_attr' => function ($iso, $key, $value) {
                    return [
                        'data-image' => '/medias/icons/flags/' . strtolower($iso) . '.svg',
                        'data-class' => 'flag mt-min',
                        'data-text' => true,
                        'data-height' => 14,
                        'data-width' => 19,
                    ];
                },
                'constraints' => [new Assert\NotBlank()]
            ]);
        } else {
            $builder->add('locale', Type\HiddenType::class, [
                'data' => $configuration->getLocale()
            ]);
        }

        $builder->add('name', Type\TextType::class, [
            'label' => false,
            'attr' => [
                'placeholder' => $this->translator->trans('Saisissez le code URL (Sans protocole)', [], 'admin')
            ],
            'constraints' => [new Assert\NotBlank()]
        ]);

        $builder->add('hasDefault', Type\CheckboxType::class, [
            'label' => false,
            'uniq_id' => false,
        ]);
    }

    /**
     * Get locales
     *
     * @param Configuration $configuration
     * @return array
     */
    private function getLocales(Configuration $configuration)
    {
        $locales = [];
        if (!empty($configuration)) {
            $locales[Languages::getName($configuration->getLocale())] = $configuration->getLocale();
            foreach ($configuration->getLocales() as $locale) {
                $locales[Languages::getName($locale)] = $locale;
            }
        }

        return $locales;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Domain::class,
            'website' => NULL,
            'website_edit' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}