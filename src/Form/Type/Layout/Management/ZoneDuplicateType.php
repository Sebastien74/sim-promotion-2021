<?php

namespace App\Form\Type\Layout\Management;

use App\Entity\Layout\Page;
use App\Entity\Layout\Zone;
use App\Repository\Core\WebsiteRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ZoneDuplicateType
 *
 * @property TranslatorInterface $translator
 * @property bool $multiSites
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ZoneDuplicateType extends AbstractType
{
    private $translator;
    private $multiSites;

    /**
     * ZoneDuplicateType constructor.
     *
     * @param TranslatorInterface $translator
     * @param WebsiteRepository $websiteRepository
     */
    public function __construct(TranslatorInterface $translator, WebsiteRepository $websiteRepository)
    {
        $this->translator = $translator;
        $this->multiSites = count($websiteRepository->findAll()) > 1;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('page', EntityType::class, [
            'mapped' => false,
            'display' => 'search',
            'label' => $this->translator->trans('Page de destination', [], 'admin'),
            'placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
            'class' => Page::class,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('p')
                    ->leftJoin('p.website', 'w')
                    ->leftJoin('p.urls', 'u')
                    ->andWhere('w.active = :active')
                    ->andWhere('u.isArchived = :isArchived')
                    ->setParameter('active', true)
                    ->setParameter('isArchived', false)
                    ->orderBy('p.adminName', 'ASC');
            },
            'attr' => ['group' => 'disable-asterisk col-12 text-center'],
            'choice_label' => function ($page) {
                if($this->multiSites) {
                    return  strip_tags($page->getAdminName()) . ' (' . $page->getWebsite()->getAdminName(). ')';
                }
                return strip_tags($page->getAdminName());
            },
            'constraints' => [new Assert\NotBlank()]
        ]);

        $builder->add('zone', EntityType::class, [
            'mapped' => false,
            'label' => false,
            'attr' => ['class' => 'd-none'],
            'class' => Zone::class,
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
            'data_class' => Zone::class,
            'website' => NULL,
            'duplicate_entity' => NULL,
            'translation_domain' => 'admin',
            'allow_extra_fields' => true
        ]);
    }
}