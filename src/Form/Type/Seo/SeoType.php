<?php

namespace App\Form\Type\Seo;

use App\Entity\Core\Website;
use App\Entity\Seo\Seo;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * SeoType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SeoType extends AbstractType
{
    private $translator;

    /**
     * SeoType constructor.
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
        /** @var Website $website */
        $website = $options['website'];

        $builder->add('metaTitle', Type\TextType::class, [
            'label' => $this->translator->trans('Méta titre', [], 'admin'),
            'counter' => 72,
            'attr' => [
                'placeholder' => $this->translator->trans("Saisissez un titre", [], 'admin'),
                'class' => "meta-title refer-code"
            ],
            'required' => false
        ]);

        $builder->add('metaTitleSecond', Type\TextType::class, [
            'label' => $this->translator->trans('Méta titre (après le tiret)', [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans("Saisissez un titre", [], 'admin'),
                'class' => "meta-title-second"
            ],
            'required' => false
        ]);

        $builder->add('metaDescription', Type\TextareaType::class, [
            'label' => $this->translator->trans('Méta description', [], 'admin'),
            'counter' => 155,
            'editor' => false,
            'attr' => [
                'placeholder' => $this->translator->trans("Éditez une description", [], 'admin'),
                'class' => "meta-description"
            ],
            'required' => false
        ]);

        $builder->add('metaCanonical', Type\TextType::class, [
            'label' => $this->translator->trans('URI Canonique', [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans("Saisissez une URI", [], 'admin'),
                'class' => "meta-canonical"
            ],
            'help' => $this->translator->trans("Sans le nom de domaine Ex: /my-page-url", [], 'admin'),
            'required' => false
        ]);

        $builder->add('breadcrumbTitle', Type\TextType::class, [
            'label' => $this->translator->trans("Titre du fil d'ariane", [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans("Saisissez un titre", [], 'admin'),
            ],
            'required' => false
        ]);

        $builder->add('noAfterDash', Type\CheckboxType::class, [
            'required' => false,
            'display' => 'button',
            'color' => 'outline-dark',
            'label' =>  $this->translator->trans('Désactiver après tiret', [], 'admin'),
            'attr' => ['group' => 'col-12', 'class' => 'w-100'],
        ]);

        $fields = ['code', 'hideInSitemap' => 'col-md-6', 'isOnline', 'isIndex' => 'col-md-6'];
        if ($options['have_index_page']) {
            $fields[] = 'indexPage';
        }
        $builder->add('url', WidgetType\UrlType::class, [
            'fields' => $fields
        ]);

        $builder->add('metaOgTitle', Type\TextType::class, [
            'label' => $this->translator->trans('Méta titre', [], 'admin'),
            'counter' => 72,
            'attr' => [
                'placeholder' => $this->translator->trans("Saisissez un titre", [], 'admin'),
                'class' => "meta-og-title"
            ],
            'help' => $this->translator->trans('La méta "après tiret" ne sera pas prise en compte', [], 'admin'),
            'required' => false
        ]);

        $builder->add('metaOgDescription', Type\TextareaType::class, [
            'label' => $this->translator->trans('Méta description', [], 'admin'),
            'counter' => 155,
            'editor' => false,
            'attr' => [
                'placeholder' => $this->translator->trans("Éditez une description", [], 'admin'),
                'class' => "meta-og-description"
            ],
            'required' => false
        ]);

        $builder->add('metaOgTwitterCard', Type\TextType::class, [
            'label' => $this->translator->trans('OG Twitter card', [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans("Saisissez le type de card", [], 'admin'),
            ],
            'required' => false
        ]);

        $builder->add('metaOgTwitterHandle', Type\TextType::class, [
            'label' => $this->translator->trans('OG Twitter handle', [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans("Saisissez le type de handle", [], 'admin'),
            ],
            'required' => false
        ]);

        $builder->add('mediaRelation', WidgetType\MediaRelationType::class, [
            'onlyMedia' => true
        ]);

        $builder->add('footerDescription', Type\TextareaType::class, [
            'label' => $this->translator->trans('Description pied de page', [], 'admin'),
            'editor' => false,
            'attr' => [
                'placeholder' => $this->translator->trans("Éditez une description", [], 'admin'),
                'class' => "footer-description"
            ],
            'required' => false
        ]);

        $builder->add('author', Type\TextType::class, [
            'label' => $this->translator->trans('Auteur', [], 'admin'),
            'attr' => [
                'placeholder' => $this->translator->trans("Saisissez un auteur", [], 'admin'),
            ],
            'required' => false
        ]);

        if ($website->getSeoConfiguration()->getMicroData()) {

            $builder->add('authorType', Type\TextType::class, [
                'label' => $this->translator->trans("Type d'auteur", [], 'admin'),
                'attr' => [
                    'placeholder' => $this->translator->trans("Saisissez un type", [], 'admin'),
                    'group' => 'col-12 p-0'
                ],
                'required' => false
            ]);

            $builder->add('metadata', Type\TextareaType::class, [
                'label' => $this->translator->trans('Script', [], 'admin'),
                'editor' => false,
                'attr' => [
                    'placeholder' => $this->translator->trans("Saisissez un script", [], 'admin'),
                    'group' => 'col-12 p-0',
                    'class' => "metadata"
                ],
                'help' => $this->translator->trans("Sans la balise <code>&lt;script></code>", [], 'admin'),
                'required' => false
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Seo::class,
            'website' => NULL,
            'have_index_page' => false,
            'translation_domain' => 'admin'
        ]);
    }
}