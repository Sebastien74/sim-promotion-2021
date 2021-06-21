<?php

namespace App\Form\Type\Core;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\MappingException;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * FilterType
 *
 * @property array FIELD_TYPES
 *
 * @property TranslatorInterface $translator
 * @property EntityManagerInterface $entityManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FilterType extends AbstractType
{
    private const FIELD_TYPES = [
        'text' => Filters\TextFilterType::class,
        'string' => Filters\TextFilterType::class,
        'datetime' => Filters\DateFilterType::class,
        'integer' => Filters\NumberFilterType::class,
        'boolean' => Filters\BooleanFilterType::class,
    ];

    private $translator;
    private $entityManager;

    /**
     * FilterType constructor.
     *
     * @param TranslatorInterface $translator
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(TranslatorInterface $translator, EntityManagerInterface $entityManager)
    {
        $this->translator = $translator;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     *
     * @throws MappingException
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $configuration = !empty($options['interface']['configuration']) ? $options['interface']['configuration'] : NULL;
        $filterName = $options['filterName'];
        $filterFields = $configuration && $configuration->$filterName ? $configuration->$filterName : [];
        $metaData = $this->entityManager->getClassMetadata($options['interface']['classname']);
        $transDomain = 'entity_' . $options['interface']['name'];
        $referEntity = new $options['interface']['classname']();

        $fieldsCount = 0;
        foreach ($filterFields as $filterField) {
            if (method_exists($referEntity, 'get' . ucfirst($filterField))) {
                $mapping = $metaData->getFieldMapping($filterField);
                if (!empty(self::FIELD_TYPES[$mapping['type']])) {
                    $fieldsCount++;
                }
            }
        }

        $groupClass = $this->getGroupClass($fieldsCount);

        foreach ($filterFields as $filterField) {
            if (method_exists($referEntity, 'get' . ucfirst($filterField))) {
                $mapping = $metaData->getFieldMapping($filterField);
                if (!empty(self::FIELD_TYPES[$mapping['type']])) {
                    $labelTranslation = $this->translator->trans($filterField, [], $transDomain);
                    $label = $labelTranslation && $labelTranslation !== $filterField ? $labelTranslation : ucfirst($filterField);
                    $arguments = [
                        'label' => $label,
                        'attr' => [
                            'group' => $groupClass,
                            'placeholder' => $this->translator->trans('Saisissez votre recherche', [], 'admin')
                        ],
                        'required' => false
                    ];
                    if (self::FIELD_TYPES[$mapping['type']] === Filters\BooleanFilterType::class) {
                        $arguments['display'] = 'search';
                        $arguments['placeholder'] = $this->translator->trans('Sélectionnez', [], 'admin');
                    }
                    $builder->add($filterField, self::FIELD_TYPES[$mapping['type']], $arguments);
                }
            }
        }
    }

    /**
     * Get group class
     *
     * @param int $fieldsCount
     * @return string
     */
    private function getGroupClass(int $fieldsCount)
    {
        if ($fieldsCount >= 4) {
            return 'col-md-3';
        } elseif ($fieldsCount === 3) {
            return 'col-md-4';
        } elseif ($fieldsCount === 2) {
            return 'col-md-6';
        } else {
            return 'col-12';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'admin',
            'interface' => [],
            'filterName' => [],
            'website' => NULL,
            'data_class' => NULL,
            'csrf_protection' => false
        ]);
    }
}