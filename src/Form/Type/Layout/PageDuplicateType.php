<?php

namespace App\Form\Type\Layout;

use App\Entity\Core\Website;
use App\Entity\Layout\Page;
use App\Form\Widget\AdminNameType;
use App\Repository\Core\WebsiteRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * PageDuplicateType
 *
 * @property TranslatorInterface $translator
 * @property WebsiteRepository $websiteRepository
 * @property Request $request
 * @property bool $isInternalUser
 * @property bool $multiSites
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class PageDuplicateType extends AbstractType
{
    private $translator;
    private $websiteRepository;
    private $request;
    private $isInternalUser;
    private $multiSites;

    /**
     * PageDuplicateType constructor.
     *
     * @param TranslatorInterface $translator
     * @param WebsiteRepository $websiteRepository
     * @param RequestStack $requestStack
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        TranslatorInterface $translator,
        WebsiteRepository $websiteRepository,
        RequestStack $requestStack,
        AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->translator = $translator;
        $this->websiteRepository = $websiteRepository;
        $this->request = $requestStack->getMasterRequest();
        $this->isInternalUser = $authorizationChecker->isGranted('ROLE_INTERNAL');
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->multiSites = count($this->websiteRepository->findAll()) > 1;
        $adminName = new AdminNameType($this->translator);
        $adminName->add($builder, ['adminNameGroup' => $this->multiSites ? 'col-md-4' : 'col-md-6']);

        $builder->add('parent', EntityType::class, [
            'required' => false,
            'display' => 'search',
            'label' => $this->translator->trans('Page parente', [], 'admin'),
            'placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
            'class' => Page::class,
            'query_builder' => function (EntityRepository $er) {
                $queryBuilder = $er->createQueryBuilder('p')
                    ->leftJoin('p.website', 'w')
                    ->leftJoin('p.urls', 'u')
                    ->andWhere('u.isArchived = :isArchived')
                    ->setParameter('isArchived', false)
                    ->orderBy('p.adminName', 'ASC');
                if(!$this->isInternalUser) {
                    $queryBuilder->andWhere('w.active = :active')
                        ->setParameter('active', true);
                }
                return $queryBuilder;
            },
            'choice_label' => function ($entity) {
                return strip_tags($entity->getAdminName());
            },
            'attr' => ['group' => $this->multiSites ? 'col-md-4' : 'col-md-6']
        ]);

        $builder->add('website', EntityType::class, [
            'label' => $this->translator->trans('Site', [], 'admin'),
            'display' => 'search',
            'class' => Website::class,
            'query_builder' => function (EntityRepository $er) {
                $queryBuilder = $er->createQueryBuilder('w')
                    ->orderBy('w.adminName', 'ASC');
                if(!$this->isInternalUser) {
                    $queryBuilder->andWhere('w.active = :active')
                        ->setParameter('active', true);
                }
                return $queryBuilder;
            },
            'data' => $this->websiteRepository->find($this->request->get('website')),
            'choice_label' => function ($entity) {
                return strip_tags($entity->getAdminName());
            },
            'attr' => ['group' => $this->multiSites ? 'col-md-4' : 'd-none']
        ]);

        $builder->add('page', EntityType::class, [
            'mapped' => false,
            'label' => false,
            'display' => false,
            'attr' => ['class' => 'd-none'],
            'class' => Page::class,
            'data' => $options['duplicate_entity'],
            'choice_label' => function ($entity) {
                return strip_tags($entity->getAdminName());
            }
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Page::class,
            'website' => NULL,
            'duplicate_entity' => NULL,
            'translation_domain' => 'admin',
            'allow_extra_fields' => true
        ]);
    }
}