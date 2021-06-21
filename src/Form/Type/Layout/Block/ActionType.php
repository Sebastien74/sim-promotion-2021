<?php

namespace App\Form\Type\Layout\Block;

use App\Entity\Core\Website;
use App\Entity\Layout\Block;
use App\Form\EventListener\Layout\ActionI18nListener;
use App\Form\Widget as WidgetType;
use App\Helper\Core\InterfaceHelper;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * ActionType
 *
 * @property TranslatorInterface $translator
 * @property InterfaceHelper $interfaceHelper
 * @property KernelInterface $kernel
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ActionType extends AbstractType
{
    private $translator;
    private $interfaceHelper;
    private $kernel;

    /**
     * ActionType constructor.
     *
     * @param TranslatorInterface $translator
     * @param InterfaceHelper $interfaceHelper
     * @param KernelInterface $kernel
     */
    public function __construct(TranslatorInterface $translator, InterfaceHelper $interfaceHelper, KernelInterface $kernel)
    {
        $this->translator = $translator;
        $this->interfaceHelper = $interfaceHelper;
        $this->kernel = $kernel;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Block $data */
        $block = $builder->getData();
        $website = $options['website'];
        $allLocales = $website->getConfiguration()->getAllLocales();
        $templates = $this->getTemplates($website, $block);
        $displayTemplates = count($templates) > 1;

        if ($displayTemplates) {
            $builder->add('template', ChoiceType::class, [
                'label' => $this->translator->trans('Template', [], 'admin'),
                'display' => 'search',
                'choices' => $templates,
                'attr' => ['group' => count($allLocales) > 1  ? 'col-12' : 'col-md-2'],
                'constraints' => [new Assert\NotBlank()]
            ]);
        } elseif ($templates) {
            $builder->add('template', HiddenType::class, [
                'data' => $templates[array_key_first($templates)]
            ]);
        }

        $builder->add('actionI18ns', CollectionType::class, [
            'label' => false,
            'entry_type' => ActionI18nType::class,
            'entry_options' => [
                'form_data' => $builder->getData(),
                'displayTemplates' => $displayTemplates,
                'website' => $options['website']
            ]
        ])->addEventSubscriber(new ActionI18nListener());

        $save = new WidgetType\SubmitType($this->translator);
        $save->add($builder, ['btn_back' => true]);
    }

    /**
     * Get template
     *
     * @param Website|null $website
     * @param Block|null $block
     * @return array
     */
    private function getTemplates(Website $website = NULL, Block $block = NULL): array
    {
        $templates = [];

        if ($website instanceof Website && $block instanceof Block) {

            $action = $block->getAction();
            $interface = $this->interfaceHelper->generate($action->getEntity());

            if (is_array($interface) && !empty($interface['actionCode']) && !empty($interface['entityCode'])) {

                $websiteTemplate = $website->getConfiguration()->getTemplate();
                $dirname = $this->kernel->getProjectDir() . '/templates/front/' . $websiteTemplate . '/actions/' . $interface['actionCode'] . '/' . $interface['entityCode'];
                $dirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $dirname);
                $filesystem = new Filesystem();

                if ($filesystem->exists($dirname)) {

                    $finder = new Finder();
                    $finder->files()->in($dirname)->depth([0]);

                    foreach ($finder as $file) {
                        if ($file->getType() === 'file') {
                            $templateName = str_replace('.html.twig', '', $file->getFilename());
                            $templates[$this->templateName($templateName)] = $templateName;
                        }
                    }
                }
            }
        }

        if (empty($templates)) {
            $templates[$this->templateName('default')] = 'default';
        }

        ksort($templates);

        return $templates;
    }

    /**
     * Get template name
     *
     * @param string $fileName
     * @return string
     */
    private function templateName(string $fileName): string
    {
        $names['default'] = $this->translator->trans('Défaut', [], 'admin');
        $names['promote-first'] = $this->translator->trans('Mise en avant de la première publication', [], 'admin');
        $names['main'] = $this->translator->trans('Principal', [], 'admin');
        $names['slider'] = $this->translator->trans('Carrousel', [], 'admin');

        return !empty($names[$fileName]) ? $names[$fileName] : $fileName;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Block::class,
            'translation_domain' => 'admin',
            'website' => NULL
        ]);
    }
}