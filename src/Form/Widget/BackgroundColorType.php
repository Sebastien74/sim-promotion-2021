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
 * BackgroundColorType
 *
 * @property TranslatorInterface $translator
 * @property Website $website
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class BackgroundColorType extends AbstractType
{
    private $translator;
    private $website;

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
            'label' => false,
            'expanded' => true,
            'choices' => $this->getColors()
        ]);
    }

    /**
     * Get Website background colors
     *
     * @return array
     */
    private function getColors(): array
    {
        $haveWhite = false;
        $colors = $this->website->getConfiguration()->getColors();
        $choices['transparent'] = NULL;

        foreach ($colors as $color) {
            if($color->getSlug() === 'bg-white') {
                $haveWhite = true;
            }
            if ($color->getCategory() === "background" && $color->getIsActive()) {
                $choices[$color->getAdminName()] = $color->getSlug();
            }
        }

        if(!$haveWhite) {
            $choices['white'] = 'bg-white';
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