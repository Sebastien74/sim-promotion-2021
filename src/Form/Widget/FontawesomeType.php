<?php

namespace App\Form\Widget;

use App\Repository\Core\IconRepository;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * FontawesomeType
 *
 * @property TranslatorInterface $translator
 * @property array $icons
 * @property KernelInterface $kernel
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FontawesomeType extends AbstractType
{
    private $translator;
    private $icons;
    private $kernel;

    /**
     * FontawesomeType constructor.
     *
     * @param TranslatorInterface $translator
     * @param IconRepository $iconRepository
     * @param KernelInterface $kernel
     */
    public function __construct(TranslatorInterface $translator, IconRepository $iconRepository, KernelInterface $kernel)
    {
        $this->translator = $translator;
        $this->icons = $iconRepository->findAll();
        $this->kernel = $kernel;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => $this->translator->trans('Icône', [], 'admin'),
            'required' => false,
            'choices' => $this->getIcons(),
            'dropdown_class' => 'icons-selector',
            'attr' => [
                'class' => 'select-icons',
                'group' => 'col-md-4'
            ],
            'choice_attr' => function ($icon, $key, $value) {
                return ['data-image' => $icon];
            }
        ]);
    }

    /**
     * Get icons
     *
     * @return array
     */
    private function getIcons(): array
    {
        $choices = [];
        $filesystem = new Filesystem();
        $assetDirname = '/medias/icons/fontawesome/';
        $dirname = $this->kernel->getProjectDir() . '/public' . $assetDirname;
        $dirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $dirname);

        if($filesystem->exists($dirname)) {

            $finder = new Finder();
            $finder->in($dirname);

            $choices[$this->translator->trans("Séléctionnez", [], 'admin')] = '';

            foreach ($finder as $file) {
                if(!empty($file->getRelativePath())) {
                    $choices[$file->getRelativePath()][$file->getRelativePathname()] = str_replace('\\', '/', $assetDirname . $file->getRelativePathname());
                }
            }
        }

        return $choices;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): ?string
    {
        return ChoiceType::class;
    }
}