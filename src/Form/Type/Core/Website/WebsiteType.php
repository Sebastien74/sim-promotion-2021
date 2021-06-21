<?php

namespace App\Form\Type\Core\Website;

use App\Entity\Core\Website;
use App\Form\Widget\AdminNameType;
use App\Form\Widget\SubmitType;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * WebsiteType
 *
 * @property TranslatorInterface $translator
 * @property KernelInterface $kernel
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class WebsiteType extends AbstractType
{
    private $translator;
    private $kernel;

    /**
     * WebsiteType constructor.
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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isNew = !$builder->getData()->getId();

        $adminName = new AdminNameType($this->translator);
        $adminName->add($builder);

        $builder->add('configuration', ConfigurationType::class, [
            'label' => false,
            'is_new' => $isNew,
            'website_edit' => $builder->getData(),
            'website' => $options['website']
        ]);

        if (!$isNew) {

            $builder->add('api', ApiType::class, [
                'label' => false,
                'website' => $options['website']
            ]);

            $builder->add('security', SecurityType::class, [
                'label' => false,
                'website' => $options['website']
            ]);
        }
        else {

            $builder->add('yaml_config', ChoiceType::class, [
                'label' => $this->translator->trans('Fichier de configuration', [], 'admin'),
                'mapped' => false,
                'display' => 'search',
                'attr' => [
                    'data-placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
                    'group' => 'col-md-3'
                ],
                'choices' => $this->getConfigFiles(),
                'constraints' => [new Assert\NotBlank()]
            ]);
        }

        $save = new SubmitType($this->translator);
        $save->add($builder);
    }

    /**
     * Get yaml configuration files
     */
    private function getConfigFiles()
    {
        $configDirname = $this->kernel->getProjectDir() . '/bin/data/config';
        $configDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $configDirname);
        $finder = new Finder();
        $finder->in($configDirname)->name('*.yaml');

        $configs = [];
        foreach ($finder as $file) {
            if (!$file->isDir()) {
                $filename = str_replace('.yaml', '', $file->getFilename());
                $configs[$filename . '.yaml'] = strtolower($filename);
            }
        }

        ksort($configs);

        return $configs;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Website::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}