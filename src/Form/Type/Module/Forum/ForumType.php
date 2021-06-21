<?php

namespace App\Form\Type\Module\Forum;

use App\Entity\Module\Forum\Forum;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * ForumType
 *
 * @property TranslatorInterface $translator
 * @property bool $isInternalUser
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ForumType extends AbstractType
{
    private $translator;
    private $isInternalUser;

    /**
     * ForumType constructor.
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
        $fields = $this->getFields();

        $adminName = new WidgetType\AdminNameType($this->translator);
        $adminName->add($builder, ['slug-internal' => $this->isInternalUser]);

        if (!$isNew && $this->isInternalUser) {

            $builder->add('fields', Type\ChoiceType::class, [
                'label' => $this->translator->trans('Champs', [], 'admin'),
                'expanded' => false,
                'multiple' => true,
                'display' => 'search',
                'attr' => [
                    'data-config' => true,
                    'group' => 'col-md-4',
                    'data-placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
                ],
                'choices' => $fields
            ]);

            $builder->add('requireFields', Type\ChoiceType::class, [
                'label' => $this->translator->trans('Champs requis', [], 'admin'),
                'expanded' => false,
                'multiple' => true,
                'display' => 'search',
                'attr' => [
                    'data-config' => true,
                    'group' => 'col-md-4',
                    'data-placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
                ],
                'choices' => $fields
            ]);

            $builder->add('formatDate', WidgetType\FormatDateType::class, [
                'attr' => ['group' => 'col-md-4', 'data-config' => true]
            ]);

            $builder->add('widgets', Type\ChoiceType::class, [
                'label' => $this->translator->trans('Widgets', [], 'admin'),
                'required' => false,
                'expanded' => false,
                'multiple' => true,
                'display' => 'search',
                'attr' => [
                    'data-config' => true,
                    'data-placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
                ],
                'choices' => [
                    $this->translator->trans('Commentaires', [], 'admin') => 'comments',
                    $this->translator->trans('Bouton de like', [], 'admin') => 'likes',
                    $this->translator->trans('Bouton de paratage', [], 'admin') => 'shares'
                ]
            ]);

            $builder->add('hideDate', Type\CheckboxType::class, [
                'required' => false,
                'label' => $this->translator->trans("Cacher la date ?", [], 'admin'),
                'attr' => ['group' => 'col-md-3', 'data-config' => true]
            ]);

            $builder->add('moderation', Type\CheckboxType::class, [
                'label' => $this->translator->trans('Activer la modération ?', [], 'admin'),
                'attr' => ['group' => 'col-md-3', 'data-config' => true]
            ]);

            $builder->add('login', Type\CheckboxType::class, [
                'label' => $this->translator->trans('Connexion requise ?', [], 'admin'),
                'attr' => ['group' => 'col-md-3', 'data-config' => true]
            ]);

            $builder->add('recaptcha', Type\CheckboxType::class, [
                'label' => $this->translator->trans('Activer le recaptcha ?', [], 'admin'),
                'attr' => ['group' => 'col-md-3', 'data-config' => true]
            ]);
        }

        $save = new WidgetType\SubmitType($this->translator);
        $save->add($builder);
    }

    /**
     * Get fields
     */
    private function getFields()
    {
        return [
            $this->translator->trans('Nom et prénom', [], 'admin') => 'authorName',
            $this->translator->trans('Message', [], 'admin') => 'message'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Forum::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}