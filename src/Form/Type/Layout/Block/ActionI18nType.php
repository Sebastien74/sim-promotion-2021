<?php

namespace App\Form\Type\Layout\Block;

use App\Entity\Module\Newscast\Listing;
use App\Entity\Module\Newscast\Teaser;
use App\Entity\Core\Website;
use App\Entity\Information\Information;
use App\Entity\Layout\ActionI18n;
use App\Entity\Layout\Block;
use App\Helper\Core\InterfaceHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * ActionI18nType
 *
 * @property TranslatorInterface $translator
 * @property InterfaceHelper $interfaceHelper
 * @property EntityManagerInterface $entityManager
 * @property Website $website
 * @property mixed $entity
 * @property int $masterField
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ActionI18nType extends AbstractType
{
    private $translator;
    private $interfaceHelper;
    private $entityManager;
    private $website;
    private $entity;
    private $masterField;

    /**
     * ActionI18nType constructor.
     *
     * @param TranslatorInterface $translator
     * @param InterfaceHelper $interfaceHelper
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(TranslatorInterface $translator, InterfaceHelper $interfaceHelper, EntityManagerInterface $entityManager)
    {
        $this->translator = $translator;
        $this->interfaceHelper = $interfaceHelper;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Block $block */
        $block = $options['form_data'];
        $className = $block->getAction()->getEntity();

        if ($className) {

            $this->website = $options['website'];
            $this->entity = new $className();
            $this->interfaceHelper->setInterface($className);
            $this->masterField = $this->interfaceHelper->getMasterField();

            $builder->add('actionFilter', ChoiceType::class, [
                'required' => false,
                'display' => 'search',
                'attr' => ['displayTemplates' => $options['displayTemplates']],
                'label' => $this->translator->trans('Filtre', [], 'admin'),
                'placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
                'choices' => $this->getChoices($className)
            ]);
        }
    }

    /**
     * Get choices values
     *
     * @param string $className
     *
     * @return array
     */
    private function getChoices(string $className)
    {
        $entity = new $className();
        $statement = $this->entityManager->getRepository($className)->createQueryBuilder('e');

        if (method_exists($this->entity, 'getUrls')) {
            $statement->join('e.urls', 'urls')
                ->andWhere('urls.isPermanentRedirect = :isPermanentRedirect')
                ->andWhere('urls.isArchived = :isArchived')
                ->orWhere('urls is NULL')
                ->setParameter(':isPermanentRedirect', false)
                ->setParameter(':isArchived', false)
                ->addSelect('urls');
        }

        if (method_exists($entity, 'getWebsite')) {
            $statement->andWhere('e.website = :website')
                ->setParameter(':website', $this->website);
        }

        if (method_exists($entity, 'getAdminName')) {
            $statement->orderBy('e.adminName');
        }

        $result = $statement->getQuery()->getResult();

        $choices = [];
        foreach ($result as $entity) {
            $label = $this->entity instanceof Information ? 'Information' : $entity->getAdminName();
            $choices[$label] = $entity->getId();
        }

        return $choices;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ActionI18n::class,
            'translation_domain' => 'admin',
            'form_data' => NULL,
            'displayTemplates' => NULL,
            'website' => NULL
        ]);
    }
}