<?php

namespace App\Form\Type\Translation;

use App\Entity\Translation\TranslationDomain;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * DomainType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class DomainType extends AbstractType
{
    private $translator;

    /**
     * TranslationType constructor.
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
        $adminName = new WidgetType\AdminNameType($this->translator);
        $adminName->add($builder);

        $builder->add('extract', CheckboxType::class, [
            'required' => false,
            'attr' => ['group' => 'col-md-3'],
            'label' => $this->translator->trans("Ajouter à l'extraction ?", [], 'admin')
        ]);

        $builder->add('forTranslator', CheckboxType::class, [
            'required' => false,
            'attr' => ['group' => 'col-md-3'],
            'label' => $this->translator->trans('Pour le traducteur ?', [], 'admin')
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TranslationDomain::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}