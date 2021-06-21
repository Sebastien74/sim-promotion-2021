<?php

namespace App\Form\Widget;

use App\Entity\Core\Website;
use App\Repository\Core\WebsiteRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * BackgroundColorSelectType
 *
 * @property TranslatorInterface $translator
 * @property Website $website
 * @property array $colors
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class BackgroundColorSelectType extends AbstractType
{
    private $translator;
    private $website;
    private $colors = [];

    /**
     * BackgroundColorType constructor.
     *
     * @param TranslatorInterface $translator
     * @param RequestStack $requestStack
     * @param WebsiteRepository $websiteRepository
     */
    public function __construct(TranslatorInterface $translator, RequestStack $requestStack, WebsiteRepository $websiteRepository)
    {
        $this->translator = $translator;
        $this->website = $websiteRepository->find($requestStack->getMasterRequest()->get('website'));
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => $this->translator->trans('Couleur de fond', [], 'admin'),
            'expanded' => false,
            'required' => false,
            'placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
            'choices' => $this->getColors(),
            'attr' => ['class' => 'select-icons'],
            'choice_attr' => function ($color, $key, $value) {
                return [
                    'data-class' => preg_match('/outline/', $color) ? 'square-outline' : 'square',
                    'data-color' => $this->colors[$color]
                ];
            }
        ]);
    }

    /**
     * Get Website background colors
     *
     * @return array
     */
    private function getColors(): array
    {
        $colors = $this->website->getConfiguration()->getColors();
        $choices = [];
        foreach ($colors as $color) {
            if ($color->getCategory() === "background" && $color->getIsActive()) {
                $choices[$color->getAdminName()] = $color->getSlug();
                $this->colors[$color->getSlug()] = $color->getColor();
            }
        }
        return $choices;
    }

    /**
     * {@inheritDoc}
     */
    public function getParent(): ?string
    {
        return ChoiceType::class;
    }
}