<?php

namespace App\Form\Type\Module\Gallery;

use App\Entity\Module\Gallery\Category;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CategoryType
 *
 * @property TranslatorInterface $translator
 * @property bool $isInternalUser
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CategoryType extends AbstractType
{
    private $translator;
    private $isInternalUser;

    /**
     * CategoryType constructor.
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
        $adminName->add($builder, [
            'adminNameGroup' => $isNew || !$this->isInternalUser ? 'col-12' : 'col-md-9'
        ]);

        if (!$isNew && $this->isInternalUser) {

            $builder->add('formatDate', WidgetType\FormatDateType::class);

            $builder->add('itemsPerGallery', Type\IntegerType::class, [
                'label' => $this->translator->trans("Nombre d'images par galerie", [], 'admin'),
                'attr' => ['group' => 'col-md-3']
            ]);

            $builder->add('displayCategory', Type\CheckboxType::class, [
                'required' => false,
                'label' => $this->translator->trans("Afficher le nom de la catégorie ?", [], 'admin'),
                'attr' => ['group' => 'col-md-3']
            ]);

            $builder->add('scrollInfinite', Type\CheckboxType::class, [
                'required' => false,
                'label' => $this->translator->trans("Scroll infinite ?", [], 'admin'),
                'attr' => ['group' => 'col-md-2']
            ]);

            $builder->add('isDefault', Type\CheckboxType::class, [
                'required' => false,
                'label' => $this->translator->trans("Catégorie principale ?", [], 'admin'),
                'attr' => ['group' => 'col-md-2']
            ]);
        }

        if (!$isNew) {

            $i18ns = new WidgetType\i18nsCollectionType($this->translator);
            $i18ns->add($builder, [
                'website' => $options['website'],
                'fields' => ['title'],
                'content_config' => false
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
            'data_class' => Category::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}