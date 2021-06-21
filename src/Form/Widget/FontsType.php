<?php

namespace App\Form\Widget;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * FontsType
 *
 * @property TranslatorInterface $translator
 * @property KernelInterface $kernel
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FontsType extends AbstractType
{
    private $translator;
    private $kernel;

    /**
     * FontsType constructor.
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
            'required' => false,
            'expanded' => false,
            'display' => 'search',
            'multiple' => true,
            'choices' => $this->getFonts()
        ]);
    }

    /**
     * Get Fonts library
     *
     * @return array
     */
    private function getFonts(): array
    {
        $fontsDirname = $this->kernel->getProjectDir() . '/assets/lib/fonts';
        $fontsDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $fontsDirname);
        $finder = new Finder();
        $finder->in($fontsDirname)->name('*.scss');

        $fonts = [];
        foreach ($finder as $file) {
            if (!$file->isDir()) {
                $filename = str_replace('.scss', '', $file->getFilename());
                $fonts[ucfirst($filename)] = strtolower($filename);
            }
        }

        ksort($fonts);

        return $fonts;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): ?string
    {
        return ChoiceType::class;
    }
}