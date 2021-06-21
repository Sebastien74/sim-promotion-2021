<?php

namespace App\Form\Type\Module\Making;

use App\Entity\Module\Making\Category;
use App\Entity\Module\Making\Making;
use App\Entity\Core\Website;
use App\Form\Widget as WidgetType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * MakingType
 *
 * @property TranslatorInterface $translator
 * @property bool $isLayoutUser
 * @property EntityManagerInterface $entityManager
 * @property Website $website
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class MakingType extends AbstractType
{
    private $translator;
    private $isLayoutUser;
    private $entityManager;
    private $website;

    /**
     * MakingType constructor.
     *
     * @param TranslatorInterface $translator
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(TranslatorInterface $translator, AuthorizationCheckerInterface $authorizationChecker, EntityManagerInterface $entityManager)
    {
        $this->translator = $translator;
        $this->isLayoutUser = $authorizationChecker->isGranted('ROLE_LAYOUT_MAKING');
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Making $data */
        $data = $builder->getData();
        $isNew = !$data->getId();
        $this->website = $options['website'];
        $displayCategory = count($this->entityManager->getRepository(Category::class)->findBy(['website' => $this->website])) > 1;

        $adminNameClass = $isNew ? 'col-md-9' : 'col-md-4';
        if (!$displayCategory) {
            $adminNameClass = $isNew ? 'col-12' : 'col-md-6';
        }

        $adminName = new WidgetType\AdminNameType($this->translator);
        $adminName->add($builder, [
            'adminNameGroup' => $adminNameClass,
            'class' => 'refer-code'
        ]);

        $builder->add('category', EntityType::class, [
            'required' => $displayCategory,
            'label' => $this->translator->trans('Catégorie', [], 'admin'),
            'display' => 'search',
            'attr' => [
                'data-placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
                'group' => $displayCategory ? "col-md-3" : 'd-none',
            ],
            'class' => Category::class,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('c')
                    ->orderBy('c.adminName', 'ASC');
            },
            'choice_label' => function ($entity) {
                return strip_tags($entity->getAdminName());
            },
            'constraints' => $displayCategory ? [new Assert\NotBlank()] : []
        ]);

        if ($isNew && $this->isLayoutUser) {
            $builder->add('customLayout', Type\CheckboxType::class, [
                'label' => $this->translator->trans("Template personnalisé ?", [], 'admin'),
                'required' => false,
                'attr' => ['group' => "col-12 text-center"]
            ]);
        }

        if (!$isNew) {

            $builder->add('promote', Type\CheckboxType::class, [
                'label' => $this->translator->trans('Mettre en avant ?', [], 'admin'),
                'attr' => [
                    'group' => "col-md-2 my-md-auto",
                ],
                'required' => false
            ]);

            if (!$data->getCustomLayout()) {
                $i18ns = new WidgetType\i18nsCollectionType($this->translator);
                $i18ns->add($builder, [
                    'website' => $options['website'],
                    'fields' => ['title' => 'col-md-6', 'subTitle' => 'col-md-4', 'introduction', 'body', 'video', 'targetLink' => 'col-md-12 add-title', 'targetPage' => 'col-md-4', 'targetLabel' => 'col-md-4', 'targetStyle' => 'col-md-4', 'newTab' => 'col-md-4']
                ]);
            }

            $urls = new WidgetType\UrlsCollectionType($this->translator);
            $urls->add($builder, ['display_seo' => true]);

            $dates = new WidgetType\PublicationDatesType($this->translator);
            $dates->add($builder);

            if ($this->isLayoutUser) {
                $builder->add('customLayout', Type\CheckboxType::class, [
                    'required' => false,
                    'label' => $this->translator->trans("Template personnalisé ?", [], 'admin'),
                    'attr' => [
                        'group' => "col-md-4 my-md-auto",
                        'data-config' => true
                    ]
                ]);
            }
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
            'data_class' => Making::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}