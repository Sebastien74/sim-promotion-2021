<?php

namespace App\Form\Widget;

use App\Entity\Core\Website;
use App\Entity\Layout\Block;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * TemplateBlockType
 *
 * @property TranslatorInterface $translator
 * @property KernelInterface $kernel
 * @property EntityManagerInterface $entityManager
 * @property Request $request
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TemplateBlockType extends AbstractType
{
    private $translator;
    private $kernel;
    private $entityManager;
    private $request;

    /**
     * TemplateBlockType constructor.
     *
     * @param TranslatorInterface $translator
     * @param KernelInterface $kernel
     * @param EntityManagerInterface $entityManager
     * @param RequestStack $requestStack
     */
    public function __construct(
        TranslatorInterface $translator,
        KernelInterface $kernel,
        EntityManagerInterface $entityManager,
        RequestStack $requestStack)
    {
        $this->translator = $translator;
        $this->kernel = $kernel;
        $this->entityManager = $entityManager;
        $this->request = $requestStack->getMasterRequest();
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $blockRequest = $this->request->get('block');
        $block = $blockRequest ? $this->entityManager->getRepository(Block::class)->find($blockRequest) : NULL;
        $templates = $this->getTemplates($block);
        $haveCustom = count($templates) > 1;

        if ($block instanceof Block) {
            $resolver->setDefaults([
                'required' => true,
                'label' => $this->translator->trans('Template', [], 'admin'),
                'display' => 'search',
                'choices' => $templates,
                'attr' => ['data-config' => $haveCustom, 'group' => $haveCustom ? 'col-md-4' : 'd-none']
            ]);
        }
    }

    /**
     * Get front templates
     *
     * @param Block $block
     * @return array
     */
    private function getTemplates(Block $block): array
    {
        $templates = [];
        $website = $this->entityManager->getRepository(Website::class)->find($this->request->get('website'));
        $blockType = $block->getBlockType()->getSlug();

        if ($website instanceof Website) {

            $frontDir = $this->kernel->getProjectDir() . '/templates/front/' . $website->getConfiguration()->getTemplate() . '/blocks/' . $blockType . '/';
            $frontDir = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $frontDir);
            $filesystem = new Filesystem();

            if ($filesystem->exists($frontDir)) {

                $finder = new Finder();
                $finder->files()->in($frontDir);

                foreach ($finder as $file) {
                    $matches = explode('.', $file->getRelativePathname());
                    $templates[$this->getTemplateName($matches[0], $blockType)] = $matches[0];
                }
            }
        }

        return $templates;
    }

    /**
     * Get template name
     *
     * @param string $name
     * @param string $blockType
     * @return string
     */
    private function getTemplateName(string $name, string $blockType): string
    {
        /** $names['block_name']['file_name'] */
        $names['link']['default'] = $this->translator->trans('Par défaut', [], 'admin');

        if (!empty($names[$blockType][$name])) {
            return $names[$blockType][$name];
        }

        return ucfirst($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): ?string
    {
        return ChoiceType::class;
    }
}