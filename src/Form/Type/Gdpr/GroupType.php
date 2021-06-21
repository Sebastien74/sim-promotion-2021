<?php

namespace App\Form\Type\Gdpr;

use App\Entity\Gdpr\Group;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * GroupType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class GroupType extends AbstractType
{
    private $translator;

    /**
     * GroupType constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isNew = !$builder->getData()->getId();

        $adminName = new WidgetType\AdminNameType($this->translator);
        $adminName->add($builder);

        if (!$isNew) {

            $builder->add('active', Type\CheckboxType::class, [
                'required' => false,
                'label' => $this->translator->trans('Activer ?', [], 'admin'),
                'attr' => ['group' => 'col-md-2']
            ]);

            $builder->add('anonymize', Type\CheckboxType::class, [
                'required' => false,
                'label' => $this->translator->trans('Anonymiser le script ?', [], 'admin'),
                'attr' => ['group' => 'col-md-3']
            ]);

            $i18ns = new WidgetType\i18nsCollectionType($this->translator);
            $i18ns->add($builder, [
                'website' => $options['website'],
                'fields' => ['title', 'introduction' => 'col-12 editor', 'body']
            ]);

            $mediaRelations = new WidgetType\MediaRelationsCollectionType($this->translator);
            $mediaRelations->add($builder, ['data_config' => true]);
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
            'data_class' => Group::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}