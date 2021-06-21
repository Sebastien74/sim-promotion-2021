<?php

namespace App\Form\Type\Security\Admin;

use App\Entity\Security\Group;
use App\Entity\Security\Role;
use App\Form\Widget as WidgetType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * GroupType
 *
 * @property TranslatorInterface $translator
 * @property RouterInterface $router
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class GroupType extends AbstractType
{
    private $translator;
    private $router;

    /**
     * GroupType constructor.
     *
     * @param TranslatorInterface $translator
     * @param RouterInterface $router
     */
    public function __construct(TranslatorInterface $translator, RouterInterface $router)
    {
        $this->translator = $translator;
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isNew = !$builder->getData()->getId();

        $adminName = new WidgetType\AdminNameType($this->translator);
        $adminName->add($builder, [
            'adminNameGroup' => $isNew ? 'col-12' : 'col-md-9'
        ]);

        if (!$isNew) {
            $builder->add('loginRedirection', ChoiceType::class, [
                'required' => false,
                'label' => $this->translator->trans("Page de redirection à la connexion", [], 'admin'),
                'placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
                'display' => 'search',
                'choices' => $this->getRoutes(),
                'attr' => ['group' => 'col-md-3'],
            ]);
        }

        $builder->add('roles', EntityType::class, [
            'label' => $this->translator->trans('Rôles', [], 'admin'),
            'class' => Role::class,
            'display' => 'search',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('m')
                    ->orderBy('m.adminName', 'ASC');
            },
            'choice_label' => function ($entity) {
                return strip_tags($entity->getAdminName());
            },
            'multiple' => true,
            'constraints' => [new Assert\Count([
                'min' => 1,
                'minMessage' => $this->translator->trans('Vous devez sélctionner au moins un groupe.', [], 'security_cms')
            ])]
        ]);

        $save = new WidgetType\SubmitType($this->translator);
        $save->add($builder);
    }

    /**
     * Get routes
     */
    private function getRoutes()
    {
        $results = [];
        $routes = $this->router->getRouteCollection();
        $allowedRoutes = ['admin_form_index'];

        foreach ($routes as $routeName => $route) {
            $isAdminRoute = preg_match('/admin_/', $routeName);
            if ($isAdminRoute && preg_match('/_index/', $routeName)
                || $isAdminRoute && preg_match('/_layout/', $routeName)
                || $isAdminRoute && preg_match('/_tree/', $routeName)) {
                if(in_array($routeName, $allowedRoutes) || preg_match_all('/{/', $route->getPath(), $m) === 1) {
                    $results[$routeName] = $routeName;
                }
            }
        }

        return $results;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Group::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}