<?php

namespace App\Form\Type\Core;

use App\Entity\Core\Module;
use App\Form\Widget as WidgetType;
use App\Repository\Security\RoleRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * ModuleType
 *
 * @property TranslatorInterface $translator
 * @property RoleRepository $roleRepository
 * @property KernelInterface $kernel
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ModuleType extends AbstractType
{
    private $translator;
    private $roleRepository;
    private $kernel;

    /**
     * ActionType constructor.
     *
     * @param TranslatorInterface $translator
     * @param RoleRepository $roleRepository
     * @param KernelInterface $kernel
     */
    public function __construct(TranslatorInterface $translator, RoleRepository $roleRepository, KernelInterface $kernel)
    {
        $this->translator = $translator;
        $this->roleRepository = $roleRepository;
        $this->kernel = $kernel;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isNew = !$builder->getData()->getId();

        $adminName = new WidgetType\AdminNameType($this->translator);
        $adminName->add($builder, ['adminNameGroup' => $isNew ? 'col-md-9' : 'col-md-4', 'slug-internal' => true]);

        $builder->add('role', Type\ChoiceType::class, [
            'required' => true,
            'display' => 'search',
            'label' => $this->translator->trans('Rôle', [], 'admin'),
            'choices' => $this->getRoles(),
            'placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
            'attr' => ['group' => 'col-md-3'],
            'constraints' => [new Assert\NotBlank()],
        ]);

        if(!$isNew) {
            $builder->add('iconClass', WidgetType\FontawesomeType::class, [
                'attr' => [
                    'class' => 'select-icons',
                    'group' => 'col-md-2'
                ],
                'constraints' => [new Assert\NotBlank()],
            ]);
        }

        $builder->add('inAdvert', Type\CheckboxType::class, [
            'required' => false,
            'label' => $this->translator->trans('Afficher dans les extensions', [], 'admin'),
            'attr' => ['group' => 'col-4', 'class' => 'w-100']
        ]);

        if(!$isNew) {

            $i18ns = new WidgetType\i18nsCollectionType($this->translator);
            $i18ns->add($builder, [
                'content_config' => false,
                'excludes_fields' => ['headerTable'],
                'website' => $options['website'],
                'fields' => ['placeholder' => 'col-md-4', 'title' => 'col-md-4', 'subTitle' => 'col-md-4', 'introduction', 'body'],
                'label_fields' => [
                    'placeholder' => $this->translator->trans('Intitulé (Administration)', [], 'admin'),
                    'title' => $this->translator->trans('Intitulé (Nos extensions)', [], 'admin'),
                    'subTitle' => $this->translator->trans('Sous-titre (Nos extensions)', [], 'admin'),
                    'introduction' => $this->translator->trans('Introduction (Nos extensions)', [], 'admin'),
                    'body' => $this->translator->trans('Description (Nos extensions)', [], 'admin')
                ],
                'placeholder_fields' => [
                    'title' => $this->translator->trans('Saisissez un intitulé', [], 'admin')
                ],
            ]);
        }

        $save = new WidgetType\SubmitType($this->translator);
        $save->add($builder);
    }

    /**
     * Get roles
     *
     * @return array
     */
    private function getRoles(): array
    {
        $roles = $this->roleRepository->findAll();
        $choices = [];

        foreach ($roles as $role) {
            $choices[$role->getName()] = $role->getName();
        }

        return $choices;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Module::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}