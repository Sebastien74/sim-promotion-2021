<?php

namespace App\Form\Type\Media;

use App\Entity\Core\Module;
use App\Entity\Core\Website;
use App\Entity\Media\Category;
use App\Form\Widget as WidgetType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;


/**
 * CategoryType
 *
 * @property TranslatorInterface $translator
 * @property AuthorizationCheckerInterface $authorizationChecker
 * @property bool $isInternalUser
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CategoryType extends AbstractType
{
    private $translator;
    private $authorizationChecker;
    private $isInternalUser;

    /**
     * FaqType constructor.
     *
     * @param TranslatorInterface $translator
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(TranslatorInterface $translator, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->translator = $translator;
        $this->authorizationChecker = $authorizationChecker;
        $this->isInternalUser = $authorizationChecker->isGranted('ROLE_INTERNAL');
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        ;

        $adminName = new WidgetType\AdminNameType($this->translator);
        $adminName->add($builder, [
            'adminNameGroup' => 'col-sm-6',
            'slug-internal' => $this->isInternalUser
        ]);

        $builder->add('module', EntityType::class, [
            'label' => $this->translator->trans("Module", [], 'admin'),
            'required' => false,
            'class' => Module::class,
            'attr' => [
                'group' => 'col-sm-3',
                'placeholder' => $this->translator->trans("Sélectionnez un module", [], 'admin')
            ],
            'choices' => $this->getModules($options['website']),
            'choice_label' => function ($entity) {
                return strip_tags($entity->getAdminName());
            },
            'display' => "search"
        ]);

        $save = new WidgetType\SubmitType($this->translator);
        $save->add($builder);
    }

    /**
     * get Modules[]
     *
     * @param Website $website
     * @return array
     */
    private function getModules(Website $website)
    {
        $modules = [];
        foreach ($website->getConfiguration()->getModules() as $module) {
            if ($this->authorizationChecker->isGranted($module->getRole())) {
                $modules[] = $module;
            }
        }
        return $modules;
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