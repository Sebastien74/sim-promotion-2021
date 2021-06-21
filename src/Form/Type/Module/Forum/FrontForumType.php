<?php

namespace App\Form\Type\Module\Forum;

use App\Entity\Module\Forum\Comment;
use App\Entity\Module\Forum\Forum;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * FrontForumType
 *
 * @property TranslatorInterface $translator
 * @property array $fields
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FrontForumType extends AbstractType
{
    private $translator;
    private static $fields = [
        'authorName', 'message'
    ];

    /**
     * FrontForumType constructor.
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
        /** @var Forum $forum */
        $forum = $options['form_data'];
        $fields = $forum->getFields();
        $requiredFields = $forum->getRequireFields();

        $recaptcha = new WidgetType\RecaptchaType($this->translator);
        $recaptcha->add($builder, $forum);

        foreach (self::$fields as $field) {
            $configuration = $this->getConfiguration($field);
            if ($configuration && in_array($field, $fields)) {
                $required = in_array($field, $requiredFields);
                $builder->add($field, $configuration['type'], [
                    'label' => $configuration['label'],
                    'required' => $required,
                    'attr' => [
                        'placeholder' => $configuration['placeholder'],
                        'autocomplete' => 'off'
                    ],
                    'constraints' => $required ? [new Assert\NotBlank()] : []
                ]);
            }
        }
    }

    /**
     * Get field configuration
     *
     * @param string $name
     * @return array|null
     */
    private function getConfiguration(string $name)
    {
        $configurations = [
            'authorName' => [
                'type' => Type\TextType::class,
                'label' => $this->translator->trans("Nom & prénom", [], 'front_form'),
                'placeholder' => $this->translator->trans("Saisissez votre nom & prénom", [], 'front_form'),
            ],
            'message' => [
                'type' => Type\TextareaType::class,
                'label' => $this->translator->trans("Message", [], 'front_form'),
                'placeholder' => $this->translator->trans("Saisissez un message", [], 'front_form'),
            ],
        ];

        return !empty($configurations[$name]) ? $configurations[$name] : NULL;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
            'form_data' => NULL,
            'translation_domain' => 'front_form'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return "front_forum";
    }
}