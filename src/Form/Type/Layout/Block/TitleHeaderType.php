<?php

namespace App\Form\Type\Layout\Block;

use App\Entity\Layout\Block;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * TitleHeaderType
 *
 * @property TranslatorInterface $translator
 * @property bool $isInternalUser
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TitleHeaderType extends AbstractType
{
    private $translator;
    private $isInternalUser;

    /**
     * TitleHeaderType constructor.
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
        $builder->add('template', WidgetType\TemplateBlockType::class);

        if ($this->isInternalUser) {

            $builder->add('backgroundColor', WidgetType\BackgroundColorSelectType::class, [
                'attr' => [
                    'group' => 'col-md-6',
                    'class' => ' select-icons',
                    'data-config' => true
                ]
            ]);

            $builder->add('color', WidgetType\AppColorType::class, [
                'attr' => [
                    'group' => 'col-md-6',
                    'class' => ' select-icons',
                    'data-config' => true
                ]
            ]);
        }

        $i18ns = new WidgetType\i18nsCollectionType($this->translator);
        $i18ns->add($builder, [
            'website' => $options['website'],
            'fields' => ['title' => 'col-md-5', 'subTitle' => 'col-md-5'],
            'fields_data' => ['titleForce' => 1]
        ]);

        $mediaRelations = new WidgetType\MediaRelationsCollectionType($this->translator);
        $mediaRelations->add($builder, ['entry_options' => ['onlyMedia' => true]]);

        $save = new WidgetType\SubmitType($this->translator);
        $save->add($builder, ['btn_back' => true]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Block::class,
            'translation_domain' => 'admin',
            'website' => NULL
        ]);
    }
}