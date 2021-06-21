<?php

namespace App\Form\Type\Seo;

use App\Entity\Core\Configuration;
use App\Entity\Seo\NotFoundUrl;
use App\Entity\Seo\Redirection;
use App\Form\Widget\SubmitType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Languages;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * RedirectionType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class RedirectionType extends AbstractType
{
    private $translator;

    /**
     * RedirectionType constructor.
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
        /** @var NotFoundUrl $notFound */
        $notFound = $options['not_found_url'];
        /** @var Configuration $configuration */
        $configuration = $options['website']->getConfiguration();
        $locales = $this->getLocales($configuration);
        $multiLocales = count($locales) > 1;

        if ($multiLocales) {

            $builder->add('locale', Type\ChoiceType::class, [
                'label' => $this->translator->trans('Langue', [], 'admin'),
                'choices' => $locales,
                'choice_translation_domain' => false,
                'attr' => ['class' => 'select-icons', 'group' => 'col-md-3'],
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

        $oldArguments = [
            'label' => $this->translator->trans('Ancienne URI / URL', [], 'admin'),
            'attr' => [
                'group' => $multiLocales ? 'col-md-4' : 'col-md-6',
                'placeholder' => $this->translator->trans("Saisissez une URI", [], 'admin')
            ],
            'constraints' => [new Assert\NotBlank()]
        ];

        if ($notFound) {
            $oldArguments['data'] = $notFound->getUri();
        }

        $builder->add('old', Type\TextType::class, $oldArguments);

        $builder->add('new', Type\TextType::class, [
            'label' => $this->translator->trans('Nouvelle URL', [], 'admin'),
            'attr' => [
                'group' => $multiLocales ? 'col-md-4' : 'col-md-6',
                'placeholder' => $this->translator->trans("Saisissez une nouvelle", [], 'admin')
            ],
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\Url()
            ]
        ]);

        if ($notFound) {
            $save = new SubmitType($this->translator);
            $save->add($builder, [
                'only_save' => true,
                'has_ajax' => true,
                'class' => 'btn-outline-white close-modal refresh'
            ]);
        }
    }

    /**
     * Get Website locales
     *
     * @param Configuration $configuration
     * @return array
     */
    private function getLocales(Configuration $configuration)
    {
        $defaultLocale = $configuration->getLocale();
        $locales[Languages::getName($defaultLocale)] = $defaultLocale;
        foreach ($configuration->getLocales() as $locale) {
            $locales[Languages::getName($locale)] = $locale;
        }

        return $locales;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Redirection::class,
            'website' => NULL,
            'not_found_url' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}