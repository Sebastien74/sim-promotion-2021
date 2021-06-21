<?php

namespace App\Form\Type\Layout\Block;

use App\Entity\Core\Website;
use App\Entity\Layout\Block;
use App\Form\Widget as WidgetType;
use App\Repository\Core\IconRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * FieldType
 *
 * @property TranslatorInterface $translator
 * @property bool $isInternalUser
 * @property IconRepository $iconRepository
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FieldType extends AbstractType
{
    private $translator;
    private $iconRepository;
    private $isInternalUser;

    /**
     * FieldType constructor.
     *
     * @param TranslatorInterface $translator
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param IconRepository $iconRepository
     */
    public function __construct(TranslatorInterface $translator, AuthorizationCheckerInterface $authorizationChecker, IconRepository $iconRepository)
    {
        $this->translator = $translator;
        $this->isInternalUser = $authorizationChecker->isGranted('ROLE_INTERNAL');
        $this->iconRepository = $iconRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Block $data */
        $block = $builder->getData();
        $isSubmit = $block->getBlockType()->getSlug() === 'form-submit';
        $isFile = $block->getBlockType()->getSlug() === 'form-file';
        $isEntity = $block->getBlockType()->getSlug() === 'form-choice-entity';
        $hasBtnField = $isSubmit || $isFile;

        $adminNameClass = $this->isInternalUser ? 'col-md-10' : 'col-12';
        if ($hasBtnField && $this->isInternalUser) {
            $adminNameClass = 'col-md-6';
        } elseif ($isEntity) {
            $adminNameClass = 'col-md-7';
        } elseif ($hasBtnField) {
            $adminNameClass = 'col-md-10';
        }

        $adminName = new WidgetType\AdminNameType($this->translator);
        $adminName->add($builder, ['adminNameGroup' => $adminNameClass]);

        if ($hasBtnField) {

            $builder->add('color', WidgetType\ButtonColorType::class, [
                'label' => $this->translator->trans('Style de bouton', [], 'admin'),
                'attr' => ['class' => 'select-icons', 'group' => 'col-md-2']
            ]);

            if ($this->isInternalUser) {
                $builder->add('icon', WidgetType\IconType::class, [
                    'attr' => ['class' => 'select-icons', 'group' => 'col-md-2'],
                    'choices' => $this->getIcons($options['website'])
                ]);
            }
        }

        $errorLabel = $this->translator->trans("Message d'erreur", [], 'admin');
        $labels = $this->getLabels($block);
        $i18ns = new WidgetType\i18nsCollectionType($this->translator);
        $i18ns->add($builder, [
            'website' => $options['website'],
            'fields' => $this->getFields($block),
            'label_fields' => ['title' => $labels->label, 'error' => $errorLabel],
            'placeholder_fields' => ['title' => $labels->placeholder],
            'content_config' => false
        ]);

        $builder->add('fieldConfiguration', FieldConfigurationType::class, [
            'label' => false,
            'field_type' => $block->getBlockType()->getSlug(),
            'website' => $options['website'],
            'block' => $block
        ]);

        $save = new WidgetType\SubmitType($this->translator);
        $save->add($builder, ['only_save' => true]);
    }

    /**
     * Get fields by BlockType
     *
     * @param Block $block
     * @return array
     */
    private function getFields(Block $block)
    {
        $type = $block->getBlockType()->getSlug();

        $fields['base'] = ['title' => 'col-md-4', 'placeholder' => 'col-md-4', 'help' => 'col-md-4'];
        $fields['form-checkbox'] = ['title' => 'col-md-12', 'help' => 'col-md-6', 'error' => 'col-md-6'];
        $fields['form-choice-entity'] = ['title' => 'col-md-3', 'placeholder' => 'col-md-3', 'help' => 'col-md-3', 'error' => 'col-md-3'];
        $fields['form-text'] = ['title' => 'col-md-3', 'placeholder' => 'col-md-3', 'help' => 'col-md-3', 'error' => 'col-md-3'];
        $fields['form-textarea'] = ['title' => 'col-md-3', 'placeholder' => 'col-md-3', 'help' => 'col-md-3', 'error' => 'col-md-3'];
        $fields['form-country'] = ['title' => 'col-md-6', 'help'];
        $fields['form-language'] = ['title' => 'col-md-6', 'help'];
        $fields['form-emails'] = ['title' => 'col-md-4', 'placeholder' => 'col-md-4', 'help' => 'col-md-4'];
        $fields['form-date'] = ['title' => 'col-md-4', 'placeholder' => 'col-md-4', 'help' => 'col-md-4'];
        $fields['form-hour'] = ['title' => 'col-md-4', 'placeholder' => 'col-md-4', 'help' => 'col-md-4'];
        $fields['form-file'] = ['title' => 'col-md-4', 'placeholder' => 'col-md-4', 'help' => 'col-md-4'];
        $fields['form-submit'] = ['title' => 'col-md-6', 'help'];
        $fields['form-hidden'] = ['title'];

        return isset($fields[$type]) ? $fields[$type] : $fields['base'];
    }

    /**
     * Get labels by BlockType
     *
     * @param Block $block
     * @return object
     */
    private function getLabels(Block $block)
    {
        $type = $block->getBlockType()->getSlug();

        $labels['base'] = (object)[
            'label' => $this->translator->trans("Label", [], 'admin'),
            'placeholder' => $this->translator->trans("Saisissez un label", [], 'admin')
        ];
        $labels['form-hidden'] = (object)[
            'label' => $this->translator->trans("Valeur", [], 'admin'),
            'placeholder' => $this->translator->trans("Saisissez une valeur", [], 'admin')
        ];

        return isset($labels[$type]) ? $labels[$type] : $labels['base'];
    }

    /**
     * Get Website icons
     *
     * @param Website $website
     * @return array
     */
    private function getIcons(Website $website)
    {
        $icons = $this->iconRepository->findBy(['configuration' => $website->getConfiguration()]);

        $choices = [];
        $choices[$this->translator->trans("Séléctionnez", [], 'admin')] = '';
        foreach ($icons as $icon) {
            $choices[$icon->getPath()] = $icon->getPath();
        }

        return $choices;
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