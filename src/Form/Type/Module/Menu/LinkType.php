<?php

namespace App\Form\Type\Module\Menu;

use App\Entity\Module\Menu\Link;
use App\Entity\Core\Website;
use App\Form\Widget as WidgetType;
use App\Form\EventListener\Translation\i18nListener;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * LinkType
 *
 * @property TranslatorInterface $translator
 * @property bool $isInternalUser
 * @property Website $website
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class LinkType extends AbstractType
{
    private $translator;
    private $isInternalUser;
    private $website;

    /**
     * LinkType constructor.
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

        if (!$isNew) {
            $adminName = new WidgetType\AdminNameType($this->translator);
            $adminName->add($builder, [
                'slug' => true,
                'adminNameGroup' => 'col-9',
                'slugGroup' => 'col-3'
            ]);
        }

        $saveOptions = [];
        if ($isNew) {
            $saveOptions['btn_save'] = true;
            $saveOptions['btn_save_label'] = $this->translator->trans('Ajouter au menu', [], 'admin');
        } else {
            $saveOptions['btn_back'] = true;
        }

        $fieldsClass = $isNew ? 'col-12' : 'col-md-4';
        $checkClass = $isNew ? 'col-12' : 'col-md-4 my-auto';

        $builder->add('i18n', WidgetType\i18nType::class, [
            'label' => false,
            'content_config' => false,
            'title_force' => false,
            'fields' => ['title' => $fieldsClass, 'placeholder' => $fieldsClass, 'targetLink' => $fieldsClass, 'targetPage' => $fieldsClass, 'newTab' => $checkClass],
            'excludes_fields' => ['targetAlignment', 'targetStyle'],
            'label_fields' => ['placeholder' => $this->translator->trans('Sous-titre', [], 'admin')],
            'placeholder_fields' => ['placeholder' => $this->translator->trans('Saisissez un sous-titre', [], 'admin')],
            'required_fields' => ['title']
        ])->addEventSubscriber(new i18nListener());

        if (!$isNew) {

            $builder->add('mediaRelation', WidgetType\MediaRelationType::class, [
                'onlyMedia' => true,
                'attr' => [
                    'data-config' => true,
                    'group' => 'col-12'
                ]
            ]);

            if ($this->isInternalUser) {

                $builder->add('color', WidgetType\AppColorType::class, [
                    'attr' => [
                        'data-config' => true,
                        'class' => 'select-icons',
                        'group' => 'col-md-4'
                    ]
                ]);

                $builder->add('backgroundColor', WidgetType\BackgroundColorSelectType::class, [
                    'attr' => [
                        'data-config' => true,
                        'class' => 'select-icons',
                        'group' => 'col-md-4'
                    ]
                ]);

                $builder->add('btnColor', WidgetType\ButtonColorType::class, [
                    'label' => $this->translator->trans('Style de bouton', [], 'admin'),
                    'attr' => [
                        'data-config' => true,
                        'class' => 'select-icons',
                        'group' => 'col-md-4'
                    ]
                ]);
            }

            $save = new WidgetType\SubmitType($this->translator);
            $save->add($builder, $saveOptions);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Link::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}