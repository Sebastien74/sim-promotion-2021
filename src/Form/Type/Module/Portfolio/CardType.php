<?php

namespace App\Form\Type\Module\Portfolio;

use App\Entity\Module\Portfolio\Card;
use App\Entity\Module\Portfolio\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CardType
 *
 * @property TranslatorInterface $translator
 * @property bool $isLayoutUser
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CardType extends AbstractType
{
    private $translator;
    private $isLayoutUser;

    /**
     * CardType constructor.
     *
     * @param TranslatorInterface $translator
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(TranslatorInterface $translator, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->translator = $translator;
        $this->isLayoutUser = $authorizationChecker->isGranted('ROLE_LAYOUT_PORTFOLIOCARD');
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Card $data */
        $card = $builder->getData();
        $isNew = !$card->getId();

        $adminName = new WidgetType\AdminNameType($this->translator);
        $adminName->add($builder, [
            'adminNameGroup' => $isNew ? 'col-12' : 'col-md-10'
        ]);

        if ($isNew && $this->isLayoutUser) {
            $builder->add('customLayout', Type\CheckboxType::class, [
                'label' => $this->translator->trans("Template personnalisé ?", [], 'admin'),
                'required' => false,
                'attr' => [
                    'group' => "col-12 text-center",
                    'data-config' => true
                ]
            ]);
        }

        if (!$isNew) {

            $urls = new WidgetType\UrlsCollectionType($this->translator);
            $urls->add($builder, ['display_seo' => true]);

            $dates = new WidgetType\PublicationDatesType($this->translator);
            $dates->add($builder);

            $builder->add('promote', Type\CheckboxType::class, [
                'required' => false,
                'display' => 'button',
                'color' => 'outline-dark',
                'label' => $this->translator->trans("Mettre en avant", [], 'admin'),
                'attr' => ['group' => 'col-md-2 d-flex align-items-end', 'class' => 'w-100']
            ]);

            $builder->add('categories', EntityType::class, [
                'label' => $this->translator->trans("Catégories", [], 'admin'),
                'required' => false,
                'display' => 'search',
                'attr' => [
                    'data-placeholder' => $this->translator->trans("Sélectionnez", [], 'admin')
                ],
                'class' => Category::class,
                'choice_label' => function ($entity) {
                    return strip_tags($entity->getAdminName());
                },
                'multiple' => true
            ]);

            if (!$card->getCustomLayout()) {
                $i18ns = new WidgetType\i18nsCollectionType($this->translator);
                $i18ns->add($builder, [
                    'website' => $options['website'],
                    'fields' => ['title' => 'col-md-6', 'subTitle' => 'col-md-4', 'introduction', 'body', 'video', 'targetLink' => 'col-md-12 add-title', 'targetPage' => 'col-md-4', 'targetLabel' => 'col-md-4', 'targetStyle' => 'col-md-4', 'newTab' => 'col-md-4']
                ]);
            }

            if ($this->isLayoutUser) {
                $builder->add('customLayout', Type\CheckboxType::class, [
                    'required' => false,
                    'display' => 'button',
                    'color' => 'outline-dark',
                    'label' => $this->translator->trans("Template personnalisé", [], 'admin'),
                    'attr' => ['group' => 'col-md-4', 'class' => 'w-100']
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
            'data_class' => Card::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}