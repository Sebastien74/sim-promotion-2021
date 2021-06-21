<?php

namespace App\Form\Type\Module\Map;

use App\Entity\Module\Map\Category;
use App\Entity\Core\Website;
use App\Entity\Media\Folder;
use App\Form\Widget as WidgetType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CategoryType
 *
 * @property TranslatorInterface $translator
 * @property bool $isInternalUser
 * @property EntityManagerInterface $entityManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CategoryType extends AbstractType
{
    private $translator;
    private $isInternalUser;
    private $entityManager;

    /**
     * TabType constructor.
     *
     * @param TranslatorInterface $translator
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(TranslatorInterface $translator, AuthorizationCheckerInterface $authorizationChecker, EntityManagerInterface $entityManager)
    {
        $this->translator = $translator;
        $this->isInternalUser = $authorizationChecker->isGranted('ROLE_INTERNAL');
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isNew = !$builder->getData()->getId();

        $adminName = new WidgetType\AdminNameType($this->translator);
        $adminName->add($builder, [
            'adminNameGroup' => $isNew ? 'col-12' : 'col-md-10'
        ]);

        if (!$isNew) {

            $builder->add('markerWidth', Type\IntegerType::class, [
                'label' => $this->translator->trans("Largeur du marker (px)", [], 'admin'),
                'attr' => [
                    'placeholder' => $this->translator->trans("Saisissez un chiffre", [], 'admin'),
                    'group' => 'col-md-4',
                    'data-config' => true
                ]
            ]);

            $builder->add('markerHeight', Type\IntegerType::class, [
                'label' => $this->translator->trans("Hauteur du marker (px)", [], 'admin'),
                'attr' => [
                    'placeholder' => $this->translator->trans("Saisissez un chiffre", [], 'admin'),
                    'group' => 'col-md-4',
                    'data-config' => true
                ]
            ]);

            $builder->add('marker', Type\ChoiceType::class, [
                'label' => $this->translator->trans('Marqueur', [], 'admin'),
                'choices' => $this->getMarkers($options['website']),
                'choice_attr' => function ($dir, $key, $value) {
                    return ['data-background' => strtolower($dir)];
                },
                'attr' => [
                    'group' => 'col-md-2 markers-select',
                    'class' => 'select-icons'
                ]
            ]);

            $i18ns = new WidgetType\i18nsCollectionType($this->translator);
            $i18ns->add($builder, [
                'website' => $options['website'],
                'content_config' => false,
                'fields' => ['title']
            ]);
        }

        $save = new WidgetType\SubmitType($this->translator);
        $save->add($builder);
    }

    /**
     * Get markers choices
     *
     * @param Website $website
     * @return array
     */
    private function getMarkers(Website $website)
    {
        $mapFolder = $this->entityManager->getRepository(Folder::class)->findOneBy([
            'website' => $website,
            'slug' => 'map'
        ]);

        $markers = [];
        foreach ($mapFolder->getMedias() as $media) {
            $markers[$media->getFilename()] = '/uploads/' . $website->getUploadDirname() . '/' . $media->getFilename();
        }

        return $markers;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}