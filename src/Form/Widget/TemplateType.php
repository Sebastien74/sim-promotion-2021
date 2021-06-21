<?php

namespace App\Form\Widget;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * TemplateType
 *
 * @property TranslatorInterface $translator
 * @property KernelInterface $kernel
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TemplateType extends AbstractType
{
    private $translator;
    private $kernel;

    /**
     * TemplateType constructor.
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
            'label' => $this->translator->trans('Template', [], 'admin'),
            'choices' => $this->getTemplates(),
            'display' => 'search',
            'translation_domain' => 'admin'
        ]);
    }

    /**
     * Get front templates
     *
     * @return array
     */
    private function getTemplates(): array
    {
        $labels = [
            'default' => $this->translator->trans('Défaut', [], 'admin')
        ];

        $templates = [];
        $frontDir = $this->kernel->getProjectDir() . '/templates/front/';
        $frontDir = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $frontDir);
        $finder = new Finder();
        $finder->files()->in($frontDir);
        foreach ($finder as $file) {
            $explodePath = explode(DIRECTORY_SEPARATOR, $file->getRelativePath());
            if (is_dir($frontDir . DIRECTORY_SEPARATOR . $file->getRelativePath()) && count($explodePath) >= 1 && !empty($explodePath[0]) && !in_array($explodePath[0], $templates)) {
                $label = !empty($labels[$explodePath[0]]) ? $labels[$explodePath[0]] : ucfirst($explodePath[0]);
                $templates[$label] = $explodePath[0];
            }
        }

        return $templates;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): ?string
    {
        return ChoiceType::class;
    }
}