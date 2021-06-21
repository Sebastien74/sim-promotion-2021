<?php

namespace App\Form\Widget;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * AdminNameType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class AdminNameType extends AbstractType
{
    private $translator;

    /**
     * AdminNameType constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Generate AdminName Type
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function add(FormBuilderInterface $builder, array $options = [])
    {
        $isNew = $builder->getData() ? !$builder->getData()->getId() : NULL;
        $haveSlug = isset($options['slug-internal']) && $options['slug-internal']
            || isset($options['slug']) && $options['slug']
            || !empty($options['slug-force']);
        $referSlugClass = !$isNew && $haveSlug || !empty($options['slug-force']) ? ' refer-code admin-name' : ' admin-name';
        $constrains = !empty($options['constrains']) && is_array($options['constrains'])
            ? $options['constrains'] : [new Assert\NotBlank()];

        $adminNameGroup = isset($options['adminNameGroup']) ? $options['adminNameGroup'] : 'col-12';
        if (empty($options['adminNameGroup']) && preg_match('/refer-code/', $referSlugClass)) {
            $adminNameGroup = 'col-sm-9';
        }

        $builder->add('adminName', TextType::class, [
            'label' => isset($options['label'])
                ? $options['label']
                : $this->translator->trans("Intitulé", [], 'admin'),
            'attr' => [
                'placeholder' => isset($options['placeholder'])
                    ? $options['placeholder']
                    : $this->translator->trans('Saisissez un intitulé', [], 'admin'),
                'class' => isset($options['class'])
                    ? $options['class'] . $referSlugClass
                    : $referSlugClass,
                'group' => $adminNameGroup,
                'data-config' => !empty($options['data_config']) ? $options['data_config'] : false
            ],
            'translation_domain' => 'admin',
            'constraints' => $constrains
        ]);

        $slugGroup = isset($options['slugGroup']) ? $options['slugGroup'] : 'col-sm-3';

        if (!$isNew && $haveSlug || !empty($options['slug-force'])) {

            $builder->add('slug', TextType::class, [
                'label' => $this->translator->trans('Code', [], 'admin'),
                'attr' => [
                    "code" => true,
                    'placeholder' => 'Saisissez un code',
                    "group" => $slugGroup
                ],
                'constraints' => [new Assert\NotBlank()]
            ]);
        }
    }
}