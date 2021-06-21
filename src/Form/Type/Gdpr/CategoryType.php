<?php

namespace App\Form\Type\Gdpr;

use App\Entity\Gdpr\Category;
use App\Form\Widget\AdminNameType;
use App\Form\Widget\SubmitType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CategoryType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CategoryType extends AbstractType
{
    private $translator;

    /**
     * CategoryType constructor.
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
        $adminName = new AdminNameType($this->translator);
        $adminName->add($builder, ['slug' => true]);

        $save = new SubmitType($this->translator);
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