<?php

namespace App\Form\Type\Module\Table;

use App\Entity\Module\Table\Table;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * TableType
 *
 * @property TranslatorInterface $translator
 * @property bool $isInternalUser
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TableType extends AbstractType
{
    private $translator;
    private $isInternalUser;

    /**
     * TableType constructor.
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

            $builder->add('cols', CollectionType::class, [
                'label' => false,
                'entry_type' => ColType::class,
                'entry_options' => ['website' => $options['website']]
            ]);

            if ($this->isInternalUser) {

                $builder->add('headBackgroundColor', WidgetType\BackgroundColorSelectType::class, [
                    'label' => $this->translator->trans("Couleur de fond de l'entête", [], 'admin'),
                    'attr' => [
                        'data-config' => true,
                        'class' => 'select-icons',
                        'group' => 'col-md-4'
                    ]
                ]);

                $builder->add('headColor', WidgetType\AppColorType::class, [
                    'label' => $this->translator->trans("Couleur de la police de l'entête", [], 'admin'),
                    'attr' => [
                        'data-config' => true,
                        'class' => 'select-icons',
                        'group' => 'col-md-4'
                    ]
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
            'data_class' => Table::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}