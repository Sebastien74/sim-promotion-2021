<?php

namespace App\Form\Widget;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Intl\Languages;
use Symfony\Component\Intl\Locales;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * LanguageIconType
 *
 * @property array LANGUAGES
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class LanguageIconType extends AbstractType
{
    private const LANGUAGES = ['fr', 'en', 'es', 'it', 'de', 'pt', 'nl', 'zh', 'ja'];

    private $translator;

    /**
     * LanguageIconType constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => $this->translator->trans('Langue', [], 'admin'),
            'multiple' => false,
            'display' => 'select-icons',
            'choices' => $this->getLanguages(),
            'choice_attr' => function ($iso, $key, $value) {
                return [
                    'data-image' => '/medias/icons/flags/' . strtolower($iso) . '.svg',
                    'data-class' => 'flag mt-min',
                    'data-text' => true,
                    'data-height' => 14,
                    'data-width' => 19,
                ];
            }
        ]);
    }

    /**
     * Get App languages
     *
     * @return array
     */
    private function getLanguages(): array
    {
        $locales = [];
        foreach (Locales::getLocales() as $locale) {
            if (in_array($locale, self::LANGUAGES)) {
                try {
                    $locales[$locale] = Languages::getName($locale);
                } catch (\Exception $exception) {
                }
            }
        }

        $languages = [];
        foreach (self::LANGUAGES as $iso) {
            if (!empty($locales[$iso])) {
                $languages[Languages::getName($iso)] = $iso;
            }
        }

        return $languages;
    }

    /**
     * {@inheritDoc}
     */
    public function getParent(): ?string
    {
        return Type\ChoiceType::class;
    }
}