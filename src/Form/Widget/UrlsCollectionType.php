<?php

namespace App\Form\Widget;

use App\Form\EventListener\Seo\UrlListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * UrlsCollectionType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class UrlsCollectionType extends AbstractType
{
    private $translator;

    /**
     * UrlsCollectionType constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Add fields
     */
    public function add(FormBuilderInterface $builder, array $options = [])
    {
        $disableTitle = !empty($options['disableTitle']) ? $options['disableTitle'] : false;
        if (!empty($options['disableTitle'])) {
            unset($options['disableTitle']);
        }

        $builder->add('urls', CollectionType::class, [
            'label' => false,
            'entry_type' => UrlType::class,
            'entry_options' => ['display_seo' => !empty($options['display_seo']) ? $options['display_seo'] : false],
            'attr' => ['disableTitle' => $disableTitle]
        ])
            ->addEventSubscriber(new UrlListener());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'admin',
            'website' => NULL,
            'display_seo' => false,
            'disableTitle' => false
        ]);
    }
}