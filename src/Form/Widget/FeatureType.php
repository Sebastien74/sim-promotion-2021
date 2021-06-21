<?php

namespace App\Form\Widget;

use App\Entity\Core\Website;
use App\Entity\Module\Catalog\Feature;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * FeatureType
 *
 * @property TranslatorInterface $translator
 * @property Website $website
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FeatureType extends AbstractType
{
    private $translator;
    private $website;

    /**
     * FeatureType constructor.
     *
     * @param TranslatorInterface $translator
     * @param RequestStack $requestStack
     */
    public function __construct(TranslatorInterface $translator, RequestStack $requestStack)
    {
        $this->translator = $translator;
        $this->website = $requestStack->getMasterRequest()->request->get('website');
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => false,
            'required' => false,
            'display' => 'search',
            'placeholder' => $this->translator->trans("Sélectionnez une caractéristique", [], 'admin'),
            'class' => Feature::class,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('f');
            },
            'choice_label' => 'adminName',
            'attr' => ['group' => ""]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): ?string
    {
        return EntityType::class;
    }
}