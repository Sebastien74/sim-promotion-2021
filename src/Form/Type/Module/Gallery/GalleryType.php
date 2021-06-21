<?php

namespace App\Form\Type\Module\Gallery;

use App\Entity\Module\Gallery\Category;
use App\Entity\Module\Gallery\Gallery;
use App\Entity\Core\Website;
use App\Form\Widget as WidgetType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * GalleryType
 *
 * @property TranslatorInterface $translator
 * @property bool $isInternalUser
 * @property Website $website
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class GalleryType extends AbstractType
{
    private $translator;
    private $isInternalUser;
    private $website;

    /**
     * GalleryType constructor.
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
            'adminNameGroup' => !$isNew && $this->isInternalUser ? 'col-md-3' : 'col-md-9',
            'slug-internal' => $this->isInternalUser
        ]);

        $builder->add('category', EntityType::class, [
            'required' => false,
            'label' => $this->translator->trans('Catégorie', [], 'admin'),
            'display' => 'search',
            'attr' => [
                'data-placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
                'group' => "col-md-3",
            ],
            'class' => Category::class,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('c')
                    ->where('c.website = :website')
                    ->setParameter('website', $this->website)
                    ->orderBy('c.adminName', 'ASC');
            },
            'choice_label' => function ($entity) {
                return strip_tags($entity->getAdminName());
            }
        ]);

        if (!$isNew && $this->isInternalUser) {

            $choices = [];
            for ($x = 1; $x <= 4; $x++) {
                $choices[$x] = $x;
            }

            $builder->add('nbrColumn', Type\ChoiceType::class, [
                'label' => $this->translator->trans("Nombre de colonne", [], 'admin'),
                'display' => 'search',
                'choices' => $choices,
                'attr' => ['group' => 'col-md-3'],
            ]);
        }

        $builder->add('popup', Type\CheckboxType::class, [
            'required' => false,
            'label' => $this->translator->trans('Afficher popup au clic des images ?', [], 'admin')
        ]);

        $save = new WidgetType\SubmitType($this->translator);
        $save->add($builder);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Gallery::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}