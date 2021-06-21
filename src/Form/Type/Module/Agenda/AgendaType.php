<?php

namespace App\Form\Type\Module\Agenda;

use App\Entity\Module\Agenda\Agenda;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * AgendaType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class AgendaType extends AbstractType
{
    private $translator;

    /**
     * AgendaType constructor.
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
        /** @var Agenda $data */
        $data = $builder->getData();
        $isNew = !$data->getId();

        $adminName = new WidgetType\AdminNameType($this->translator);
        $adminName->add($builder, [
            'slug' => true,
            'class' => 'refer-code'
        ]);

        if (!$isNew) {

            $i18ns = new WidgetType\i18nsCollectionType($this->translator);
            $i18ns->add($builder, [
                'website' => $options['website'],
                'content_config' => false,
                'target_config' => false,
                'excludes_fields' => ['headerTable'],
                'label_fields' => [
                    'introduction' => $this->translator->trans("Information", [], 'admin'),
                    'targetLink' => $this->translator->trans("URL", [], 'admin')
                ],
                'fields' => ['title', 'introduction', 'targetLink']
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
            'data_class' => Agenda::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}