<?php

namespace App\Form\Type\Module\Catalog;

use App\Form\Widget as WidgetType;
use App\Entity\Module\Catalog\Catalog;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CatalogType
 *
 * @property TranslatorInterface $translator
 * @property bool $isInternalUser
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CatalogType extends AbstractType
{
    private $translator;
    private $isInternalUser;

    /**
     * CatalogType constructor.
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

        $adminName = new WidgetType\AdminNameType($this->translator);
        $adminName->add($builder, ['slug-internal' => $this->isInternalUser]);

        if (!$isNew) {

            if($this->isInternalUser) {
                $builder->add('information', Type\CheckboxType::class, [
                    'required' => false,
                    'display' => 'button',
                    'color' => 'outline-dark',
                    'label' => $this->translator->trans('Activer les coordonnées', [], 'admin'),
                    'attr' => ['group' => 'col-md-4', 'class' => 'w-100', 'data-config' => true]
                ]);
            }

            $i18ns = new WidgetType\i18nsCollectionType($this->translator);
            $i18ns->add($builder, [
                'content_config' => false,
                'fields' => ['title'],
                'disableTitle' => true,
            ]);

            $mediaRelations = new WidgetType\MediaRelationsCollectionType($this->translator);
            $mediaRelations->add($builder, [
                'data_config' => true,
                'entry_options' => ['onlyMedia' => true]
            ]);
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
            'data_class' => Catalog::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}