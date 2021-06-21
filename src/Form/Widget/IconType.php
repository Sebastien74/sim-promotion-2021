<?php

namespace App\Form\Widget;

use App\Entity\Core\Icon;
use App\Entity\Core\Website;
use App\Repository\Core\IconRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * IconType
 *
 * @property TranslatorInterface $translator
 * @property array $icons
 * @property KernelInterface $kernel
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class IconType extends AbstractType
{
    private $translator;
    private $icons;
    private $kernel;

    /**
     * IconType constructor.
     *
     * @param TranslatorInterface $translator
     * @param EntityManagerInterface $entityManager
     * @param RequestStack $requestStack
     * @param KernelInterface $kernel
     */
    public function __construct(
        TranslatorInterface $translator,
        EntityManagerInterface $entityManager,
        RequestStack $requestStack,
        KernelInterface $kernel)
    {
        $request = $requestStack->getMasterRequest();
        $website = $entityManager->getRepository(Website::class)->find($request->get('website'));

        $this->translator = $translator;
        $this->icons = $entityManager->getRepository(Icon::class)->findBy(['configuration' => $website->getConfiguration()]);
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
     * Get Website icons
     *
     * @return array
     */
    private function getIcons(): array
    {
        $choices = [];
        $choices[$this->translator->trans("Séléctionnez", [], 'admin')] = '';
        foreach ($this->icons as $icon) {
            $choices[$icon->getPath()] = $icon->getPath();
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