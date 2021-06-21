<?php

namespace App\Form\Type\Layout\Management;

use App\Entity\Core\Website;
use App\Entity\Layout\Block;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * BlockConfigurationType
 *
 * @property TranslatorInterface $translator
 * @property bool $isInternalUser
 * @property Website $website
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class BlockConfigurationType extends AbstractType
{
    private $translator;
    private $isInternalUser;
    private $website;

    /**
     * BlockConfigurationType constructor.
     *
     * @param TranslatorInterface $translator
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(TranslatorInterface $translator, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->translator = $translator;
        $this->isInternalUser = $authorizationChecker->isGranted('ROLE_INTERNAL');
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Block $block */
        $block = $builder->getData();
        $this->website = $options['website'];

        $margins = new MarginType($this->translator);
        $margins->add($builder);

        $builder->add('alignment', AlignmentType::class, [
            'label' => false
        ]);

        $builder->add('verticalAlign', Type\CheckboxType::class, [
            'required' => false,
            'display' => 'button',
            'color' => 'outline-dark',
            'label' => $this->translator->trans('Centrer verticalement le contenu du bloc', [], 'admin'),
            'attr' => ['group' => 'col-12', 'class' => 'w-100']
        ]);

        $builder->add('endAlign', Type\CheckboxType::class, [
            'required' => false,
            'display' => 'button',
            'color' => 'outline-dark',
            'label' => $this->translator->trans('Aligner le contenu en bas du bloc', [], 'admin'),
            'attr' => ['group' => 'col-12', 'class' => 'w-100']
        ]);

        $builder->add('hide', HideType::class, [
            'label' => $this->translator->trans('Cacher le bloc', [], 'admin'),
            'attr' => ['group' => 'col-12', 'class' => 'w-100']
        ]);

        $builder->add('hideMobile', HideMobileType::class, [
            'label' => $this->translator->trans("Cacher le bloc sur mobile", [], 'admin'),
            'attr' => ['group' => 'col-md-6', 'class' => 'w-100']
        ]);

        $builder->add('reverse', Type\CheckboxType::class, [
            'required' => false,
            'display' => 'button',
            'color' => 'outline-dark',
            'label' => $this->translator->trans("Afficher le bloc en première position sur mobile", [], 'admin'),
            'attr' => ['group' => 'col-md-6', 'class' => 'w-100']
        ]);

        $margins = new ScreensType($this->translator);
        $margins->add($builder, [
            'entity' => $builder->getData()->getCol(),
            'mobilePositionLabel' => true,
            'tabletPositionLabel' => true,
            'mobileSizeLabel' => true,
            'tabletSizeLabel' => true
        ]);

        $transitions = new TransitionType($this->translator);
        $transitions->add($builder, ['website' => $this->website]);

        $builder->add('save', Type\SubmitType::class, [
            'label' => $this->translator->trans('Enregistrer', [], 'admin'),
            'attr' => ['class' => 'btn-info edit-element-submit-btn']
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Block::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}