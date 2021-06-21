<?php

namespace App\Form\Widget;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * AdminThemeType
 *
 * @property TranslatorInterface $translator
 * @property KernelInterface $kernel
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class AdminThemeType extends AbstractType
{
    private $translator;
    private $kernel;

    /**
     * AdminThemeType constructor.
     *
     * @param TranslatorInterface $translator
     * @param KernelInterface $kernel
     */
    public function __construct(TranslatorInterface $translator, KernelInterface $kernel)
    {
        $this->translator = $translator;
        $this->kernel = $kernel;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => $this->translator->trans('Admin thème', [], 'admin'),
            'placeholder' => $this->translator->trans('Séléctionnez', [], 'admin'),
            'required' => false,
            'choices' => $this->getTemplates(),
            'display' => 'search',
            'translation_domain' => 'admin'
        ]);
    }

    /**
     * Get admin templates
     *
     * @return array
     */
    private function getTemplates(): array
    {
        $labels = [
            'default' => $this->translator->trans('Classique', [], 'admin'),
            'clouds' => $this->translator->trans('Nuages', [], 'admin'),
            'felix' => $this->translator->trans('Félix', [], 'admin')
        ];

        $themes = [];
        $dirname = $this->kernel->getProjectDir() . '/assets/scss/admin/themes';
        $dirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $dirname);
        $finder = new Finder();
        $finder->in($dirname);

        $themes['Anonyme'] = NULL;
        foreach ($finder as $file) {
            $filename = $file->getFilename();
            if(preg_match('/-vendor/', $filename)) {
                $theme = str_replace('-vendor.scss', '', $filename);
                $label = !empty($labels[$theme]) ? $labels[$theme] : ucfirst($theme);
                $themes[$label] = $theme;
            }
        }

        return $themes;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): ?string
    {
        return ChoiceType::class;
    }
}