<?php

namespace App\Form\Type\Module\Menu;

use App\Entity\Module\Menu\Menu;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * MenuType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class MenuType extends AbstractType
{
    private $translator;

    /**
     * MenuType constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isNew = !$builder->getData()->getId();

        $adminName = new WidgetType\AdminNameType($this->translator);
        $adminName->add($builder, [
            'slug' => true,
            'adminNameGroup' => 'col-12',
            'slugGroup' => 'col-12'
        ]);

        if (!$isNew) {

            $builder->add('template', Type\ChoiceType::class, [
                'label' => $this->translator->trans('Template', [], 'admin'),
                'display' => 'search',
                'choices' => [
                    $this->translator->trans("Principal", [], 'admin') => "main",
                    $this->translator->trans("Classique", [], 'admin') => "bootstrap",
                    $this->translator->trans("Megamenu", [], 'admin') => "megamenu",
                    $this->translator->trans("Pied de page", [], 'admin') => "footer"
                ]
            ]);

            $builder->add('expand', Type\ChoiceType::class, [
                'label' => $this->translator->trans('Breakpoint', [], 'admin'),
                'display' => 'search',
                'choices' => [
                    $this->translator->trans("SM", [], 'admin') => "sm",
                    $this->translator->trans("MD", [], 'admin') => "md",
                    $this->translator->trans("LG", [], 'admin') => "lg",
                    $this->translator->trans("XL", [], 'admin') => "xl",
                    $this->translator->trans("XXL", [], 'admin') => "xxl",
                    $this->translator->trans("XXXL", [], 'admin') => "xxxl",
                ]
            ]);

            $builder->add('size', Type\ChoiceType::class, [
                'label' => $this->translator->trans('Taille', [], 'admin'),
                'display' => 'search',
                'choices' => [
                    $this->translator->trans("Conteneur", [], 'admin') => "container",
                    $this->translator->trans("Toute la largeur", [], 'admin') => "container-fluid"
                ]
            ]);

            $builder->add('alignment', Type\ChoiceType::class, [
                'label' => $this->translator->trans('Alignement', [], 'admin'),
                'display' => 'search',
                'choices' => [
                    $this->translator->trans("Gauche", [], 'admin') => "left",
                    $this->translator->trans("Centré", [], 'admin') => "center",
                    $this->translator->trans("Droite", [], 'admin') => "right"
                ]
            ]);

            $builder->add('maxLevel', Type\IntegerType::class, [
                'label' => $this->translator->trans("Nombre maximum de niveau", [], 'admin'),
                'attr' => [
                    'placeholder' => $this->translator->trans("Saisissez un chiffre", [], 'admin')
                ],
                'required' => false
            ]);

            $builder->add('isMain', Type\CheckboxType::class, [
                'required' => false,
                'display' => 'button',
                'color' => 'outline-dark',
                'label' => $this->translator->trans('Menu principal', [], 'admin'),
                'attr' => ['class' => 'w-100']
            ]);

            $builder->add('isFooter', Type\CheckboxType::class, [
                'required' => false,
                'display' => 'button',
                'color' => 'outline-dark',
                'label' => $this->translator->trans('Pied de page principal', [], 'admin'),
                'attr' => ['class' => 'w-100']
            ]);

            $builder->add('fixedOnScroll', Type\CheckboxType::class, [
                'required' => false,
                'display' => 'button',
                'color' => 'outline-dark',
                'label' => $this->translator->trans('Fixe au scroll', [], 'admin'),
                'attr' => ['class' => 'w-100']
            ]);

            $builder->add('dropdownHover', Type\CheckboxType::class, [
                'required' => false,
                'display' => 'button',
                'color' => 'outline-dark',
                'label' => $this->translator->trans('Ouvrir les sous-menus au survol', [], 'admin'),
                'attr' => ['class' => 'w-100']
            ]);
        }

        $save = new WidgetType\SubmitType($this->translator);
        $save->add($builder);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Menu::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}