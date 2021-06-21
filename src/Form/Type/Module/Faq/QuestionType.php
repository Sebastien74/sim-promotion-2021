<?php

namespace App\Form\Type\Module\Faq;

use App\Entity\Module\Faq\Question;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * QuestionType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class QuestionType extends AbstractType
{
    private $translator;

    /**
     * QuestionType constructor.
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

            $mediaRelations = new WidgetType\MediaRelationsCollectionType($this->translator);
            $mediaRelations->add($builder, [
                'data_config' => true,
                'entry_options' => ['onlyMedia' => true]
            ]);

            $i18ns = new WidgetType\i18nsCollectionType($this->translator);
            $i18ns->add($builder, [
                'fields' => ['title', 'introduction', 'body'],
                'excludes_fields' => ['headerTable'],
                'content_config' => false,
                'label_fields' => [
                    'title' => $this->translator->trans('Question', [], 'admin'),
                    'body' => $this->translator->trans('Réponse', [], 'admin')
                ]
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
            'data_class' => Question::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}