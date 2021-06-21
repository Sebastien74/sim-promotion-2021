<?php

namespace App\Form\Type\Module\Form;

use App\Entity\Module\Form\Form;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * FormType
 *
 * @property TranslatorInterface $translator
 * @property bool $isInternalUser
 * @property Request $request
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FormType extends AbstractType
{
    private $translator;
    private $isInternalUser;
    private $request;

    /**
     * FormType constructor.
     *
     * @param TranslatorInterface $translator
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param RequestStack $requestStack
     */
    public function __construct(
        TranslatorInterface $translator,
        AuthorizationCheckerInterface $authorizationChecker,
        RequestStack $requestStack)
    {
        $this->translator = $translator;
        $this->isInternalUser = $authorizationChecker->isGranted('ROLE_INTERNAL');
        $this->request = $requestStack->getMasterRequest();
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Form $form */
        $form = $builder->getData();
        $isNew = !$form->getId();

        $adminName = new WidgetType\AdminNameType($this->translator);
        $adminName->add($builder, ['slug-internal' => $this->isInternalUser]);

        if (!$isNew && !$form->getStepform()) {

            $i18ns = new WidgetType\i18nsCollectionType($this->translator);
            $i18ns->add($builder, [
                'website' => $options['website'],
                'data_config' => true,
                'content_config' => false,
                'fields' => ['title' => 'col-md-6', 'subTitle' => 'col-md-6', 'body', 'placeholder' => 'col-12'],
                'excludes_fields' => ['headerTable'],
                'label_fields' => [
                    'title' => $this->translator->trans("Objet du mail de reception", [], 'admin'),
                    'subTitle' => $this->translator->trans("Objet du mail de confirmation", [], 'admin'),
                    'body' => $this->translator->trans("Corps du mail de confirmation", [], 'admin'),
                    'placeholder' => $this->translator->trans('Message de remerciement sur le site', [], 'admin'),
                ],
                'placeholder_fields' => [
                    'title' => $this->translator->trans("Saisissez un objet", [], 'admin'),
                    'subTitle' => $this->translator->trans("Saisissez un objet", [], 'admin'),
                    'body' => $this->translator->trans("Saisissez un message", [], 'admin'),
                    'placeholder' => $this->translator->trans('Saisissez un message', [], 'admin'),
                ]
            ]);

            $builder->add('configuration', ConfigurationType::class, [
                'label' => false,
                'is_new' => $isNew,
                'website' => $options['website'],
                'entity' => $form->getConfiguration(),
                'attr' => ['data-config' => true]
            ]);

        } elseif (!$isNew) {

            $i18ns = new WidgetType\i18nsCollectionType($this->translator);
            $i18ns->add($builder, [
                'website' => $options['website'],
                'content_config' => false,
                'fields' => ['title' => 'col-md-12'],
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
            'data_class' => Form::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}