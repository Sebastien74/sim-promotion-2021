<?php

namespace App\Form\Type\Media;

use App\Entity\Core\Website;
use App\Entity\Media\Folder;
use App\Form\Widget as WidgetType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * FolderType
 *
 * @property TranslatorInterface $translator
 * @property bool $isInternalUser
 * @property Website $website
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FolderType extends AbstractType
{
    private $translator;
    private $isInternalUser;
    private $website;

    /**
     * FolderType constructor.
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
        $isNew = !$builder->getData()->getId();
        $this->website = $options['website'];

        $adminName = new WidgetType\AdminNameType($this->translator);
        $adminName->add($builder, [
            'adminNameGroup' => $this->isInternalUser && !$isNew ? 'col-md-6' : 'col-md-8',
            'slug-internal' => $this->isInternalUser
        ]);

        $builder->add('parent', EntityType::class, [
            'required' => false,
            'display' => 'search',
            'label' => $this->translator->trans('Dossier parent', [], 'admin'),
            'attr' => [
                'data-placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
                'group' => $this->isInternalUser && !$isNew ? 'col-md-3' : 'col-md-4',
            ],
            'class' => Folder::class,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('f')
                    ->where('f.website = :website')
                    ->setParameter('website', $this->website)
                    ->orderBy('f.adminName', 'ASC');
            },
            'choice_label' => function ($entity) {
                return strip_tags($entity->getAdminName());
            }
        ]);

        $save = new WidgetType\SubmitType($this->translator);
        $save->add($builder, [
            'only_save' => true,
            'has_ajax' => true,
            'refresh' => true
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Folder::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}