<?php

namespace App\Form\Widget;

use App\Entity\Core\Configuration;
use App\Entity\Core\Website;
use App\Entity\Media\Category;
use App\Entity\Media\Media;
use App\Form\EventListener\Media\VideoListener;
use App\Form\Validator\File;
use App\Form\Validator\UniqFile;
use App\Form\Validator\UniqFileName;
use App\Form\Widget as WidgetType;
use App\Repository\Core\WebsiteRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * MediaType
 *
 * @property string MAX_SIZE
 * @property string ACCEPT
 * @property array MIME_TYPES
 *
 * @property TranslatorInterface $translator
 * @property Request $request
 * @property WebsiteRepository $websiteRepository
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class MediaType extends AbstractType
{
    private const MAX_SIZE = '200M';
    private const ACCEPT = '.xlsx, .xls, image/*, .doc, .docx, .ppt, .pptx, .txt, .pdf, .webmanifest, .mp4, .m4v, .mov, .webm, .ogg, .ogv';
    private const MIME_TYPES = [
        'image/*',
        'video/*',
        'application/pdf',
        'application/msword',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'text/plain'
    ];

    private $translator;

    /**
     * MediaType constructor.
     *
     * @param TranslatorInterface $translator
     * @param RequestStack $requestStack
     * @param WebsiteRepository $websiteRepository
     */
    public function __construct(TranslatorInterface $translator, RequestStack $requestStack, WebsiteRepository $websiteRepository)
    {
        $this->translator = $translator;
        $this->request = $requestStack->getMasterRequest();
        $this->websiteRepository = $websiteRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $websiteRequest = $this->request->get('website');
        /** @var Website $website */
        $website = !empty($options['website']) ? $options['website']
            : ($websiteRequest ? $this->websiteRepository->find($websiteRequest) : NULL);
        $configuration = $website instanceof Website ? $website->getConfiguration() : NULL;
        $categoriesActivated = $configuration instanceof Configuration ? $configuration->getMediasCategoriesStatus() : false;

        $builder->add('uploadedFile', FileType::class, [
            'label' => false,
            'mapped' => false,
            'multiple' => $options['multiple'],
            'required' => false,
            'attr' => [
                'onlyMedia' => $options['onlyMedia'],
                'accept' => $options['onlyVideo'] ? 'video/*' : self::ACCEPT,
                'data-max-size' => self::MAX_SIZE,
                'placeholder' => $this->translator->trans('Séléctionnez une image', [], 'admin'),
                'class' => !$options['multiple'] ? 'dropify' : 'dropzone-field',
                'group' => !$options['multiple'] ? 'dropify-group' : 'd-none',
                'data-height' => $options['dataHeight']
            ],
            'constraints' => [
                new File([
                    'maxSize' => self::MAX_SIZE,
                    'mimeTypes' => $options['onlyVideo'] ? 'video/*' : self::MIME_TYPES
                ]),
                new UniqFile()
            ]
        ]);

        if (!empty($options['copyright'])) {

            $builder->add('copyright', Type\TextType::class, [
                'required' => false,
                'label' => $this->translator->trans('Copyright', [], 'admin'),
                'attr' => [
                    'placeholder' => $this->translator->trans('Saisissez le copyright', [], 'admin'),
                ]
            ]);

            $builder->add('notContractual', Type\CheckboxType::class, [
                'required' => false,
                'display' => 'button',
                'color' => 'outline-dark',
                'label' => $this->translator->trans('Image non contractuelle', [], 'admin'),
                'attr' => ['class' => 'w-100']
            ]);
        }

        if (!empty($options['titlePosition'])) {
            $builder->add('titlePosition', Type\ChoiceType::class, [
                'display' => 'search',
                'choices' => [
                    $this->translator->trans('En haut', [], 'admin') => 'top',
                    $this->translator->trans('En bas', [], 'admin') => 'bottom',
                    $this->translator->trans('À gauche', [], 'admin') => 'left',
                    $this->translator->trans('À droite', [], 'admin') => 'right',
                    $this->translator->trans("Sur l'image", [], 'admin') => 'in-box',
                ],
                'label' => $this->translator->trans('Position du titre', [], 'admin'),
                'attr' => [
                    'placeholder' => $this->translator->trans('Sélectionnez', [], 'admin')
                ]
            ]);
        }

        if (!empty($options['quality'])) {
            $builder->add('quality', Type\IntegerType::class, [
                'required' => false,
                'label' => $this->translator->trans('Qualité', [], 'admin'),
                'attr' => [
                    'placeholder' => $this->translator->trans('Saisissez une valeur', [], 'admin'),
                    'min' => 1,
                    'max' => 100,
                ],
                'constraints' => [new Assert\Range(['min' => 1, 'max' => 100])]
            ]);
        }

        if ($categoriesActivated && !empty($options['categories'])) {

            $builder->add('categories', EntityType::class, [
                'label' => $this->translator->trans('Catégories', [], 'admin'),
                'class' => Category::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('m')
                        ->orderBy('m.adminName', 'ASC');
                },
                'attr' => [
                    'data-placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
                ],
                'choice_label' => function ($entity) {
                    return strip_tags($entity->getAdminName());
                },
                'multiple' => true,
                'required' => false,
                'display' => 'search'
            ]);
        }

        if ($options['edition'] && $options['screen'] || $options['video']) {

            $builder->add('mediaScreens', CollectionType::class, [
                'label' => false,
                'entry_type' => MediaType::class,
                'entry_options' => [
                    'name' => 'col-12',
                    'onlyVideo' => $options['video'],
                    'edition' => !$options['video'],
                    'copyright' => $options['copyright'],
                    'quality' => $options['quality'],
                    'screen' => false
                ]
            ]);

            if ($options['video']) {
                $builder->addEventSubscriber(new VideoListener());
            }
        }

        if ($options['edition']) {

            $builder->add('name', Type\TextType::class, [
                'required' => false,
                'label' => $this->translator->trans('Nom du fichier', [], 'admin'),
                'attr' => [
                    'placeholder' => $this->translator->trans('Saisissez un nom de fichier', [], 'admin'),
                    'group' => !empty($options['name']) ? $options['name'] : 'col-md-6'
                ],
                'constraints' => [
                    new UniqFileName()
                ]
            ]);

            $adminName = new WidgetType\i18nsCollectionType($this->translator);
            $adminName->add($builder, [
                'website' => $options['website'],
                'fields' => ['title'],
                'placeholder_fields' => ['title' => $this->translator->trans('Titre - Alt', [], 'admin')],
                'excludes_fields' => ['headerTable'],
                'content_config' => false,
                'title_force' => false
            ]);

            if ($options['screen']) {
                $builder->add('save', Type\SubmitType::class, [
                    'label' => $this->translator->trans('Enregistrer', [], 'admin'),
                    'attr' => ['class' => 'btn-info ajax-post refresh']
                ]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Media::class,
            'translation_domain' => 'admin',
            'website' => NULL,
            'edition' => false,
            'name' => NULL,
            'dataHeight' => NULL,
            'titlePosition' => false,
            'copyright' => false,
            'quality' => false,
            'categories' => false,
            'onlyVideo' => false,
            'onlyMedia' => false,
            'video' => false,
            'screen' => true,
            'multiple' => false
        ]);
    }
}