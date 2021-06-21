<?php

namespace App\Form\Widget;

use App\Entity\Media\MediaRelation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * MediaRelationType
 *
 * @property TranslatorInterface $translator
 * @property bool $isInternalUser
 * @property array $options
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class MediaRelationType extends AbstractType
{
    private $translator;
    private $isInternalUser;
    private $options = [];

    /**
     * MediaRelationType constructor.
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
        $builder->add('media', MediaType::class, [
            'label' => false,
            'titlePosition' => $options['titlePosition'],
            'copyright' => $options['copyright'],
            'categories' => $options['categories'],
            'screen' => $options['screen'],
            'video' => $options['video'],
            'onlyMedia' => $options['onlyMedia'],
            'dataHeight' => $options['dataHeight'],
        ]);

        if (!$options['onlyMedia']) {

            $builder->add('maxWidth', Type\IntegerType::class, [
                'required' => false,
                'label' => $this->translator->trans('Largeur (px)', [], 'admin'),
                'attr' => [
                    'placeholder' => $this->translator->trans('Saisissez une largeur', [], 'admin')
                ]
            ]);

            $builder->add('maxHeight', Type\IntegerType::class, [
                'required' => false,
                'label' => $this->translator->trans('Hauteur (px)', [], 'admin'),
                'attr' => [
                    'placeholder' => $this->translator->trans('Saisissez une hauteur', [], 'admin')
                ]
            ]);

            $builder->add('downloadable', Type\CheckboxType::class, [
                'required' => false,
                'display' => 'button',
                'color' => 'outline-dark',
                'label' => $this->translator->trans('Téléchargeable', [], 'admin'),
                'attr' => ['class' => 'w-100']
            ]);

            $builder->add('displayTitle', Type\CheckboxType::class, [
                'required' => false,
                'display' => 'button',
                'color' => 'outline-dark',
                'label' => $this->translator->trans('Afficher le titre', [], 'admin'),
                'attr' => ['class' => 'w-100']
            ]);

            $builder->add('popup', Type\CheckboxType::class, [
                'required' => false,
                'display' => 'button',
                'color' => 'outline-dark',
                'label' => $this->translator->trans('Afficher popup au clic', [], 'admin'),
                'attr' => ['class' => 'w-100']
            ]);

            $builder->add('isMain', Type\CheckboxType::class, [
                'required' => false,
                'display' => 'button',
                'color' => 'outline-dark',
                'label' => $this->translator->trans('Image principale', [], 'admin'),
                'attr' => ['class' => 'w-100']
            ]);

            if (!empty($options['fields']['i18n'])) {
                $builder->add('i18n', i18nType::class, [
                    'label' => false,
                    'content_config' => $options['i18n_content_config'],
                    'fields' => $options['fields']['i18n'],
                    'excludes_fields' => ['headerTable'],
                    'website' => $options['website']
                ]);
            }

            $this->options = $options;

            foreach ($options['fields'] as $key => $field) {
                if (is_string($field)) {
                    $getter = 'get' . ucfirst($field);
                    if (method_exists($this, $getter)) {
                        $this->$getter($builder, $field);
                    }
                }
            }
        }

        if ($options['active']) {
            $builder->add('isActive', Type\CheckboxType::class, [
                'required' => false,
                'display' => 'button',
                'color' => 'outline-dark',
                'label' => $this->translator->trans('Activer', [], 'admin'),
                'attr' => ['class' => 'w-100']
            ]);
        }
    }

    /**
     * Submit field
     *
     * @param FormBuilderInterface $builder
     * @param string $field
     */
    private function getSubmit(FormBuilderInterface $builder, string $field)
    {
        $saveOptions = [
            'btn_save' => true,
            'force' => true,
            'class' => 'btn-info ajax-post inner-preloader-btn w-100'
        ];
        $save = new SubmitType($this->translator);
        $save->add($builder, $saveOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => false,
            'data_class' => MediaRelation::class,
            'website' => NULL,
            'screen' => false,
            'video' => false,
            'titlePosition' => false,
            'copyright' => false,
            'categories' => false,
            'onlyMedia' => false,
            'active' => false,
            'i18n_content_config' => true,
            'dataHeight' => NULL,
            'fields' => [],
            'required_fields' => [],
            'translation_domain' => 'admin'
        ]);
    }
}