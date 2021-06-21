<?php

namespace App\Form\Type\Module\Form;

use App\Entity\Module\Form\StepForm;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * StepFormType
 *
 * @property TranslatorInterface $translator
 * @property bool $isInternalUser
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class StepFormType extends AbstractType
{
    private $translator;
    private $isInternalUser;

    /**
     * StepFormType constructor.
     *
     * @param TranslatorInterface $translator
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(TranslatorInterface $translator, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->translator = $translator;
        $this->isInternalUser = $authorizationChecker->isGranted('ROLE_INTERNAL');
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var StepForm $form */
        $form = $builder->getData();
        $isNew = !$builder->getData()->getId();

        $adminName = new WidgetType\AdminNameType($this->translator);
        $adminName->add($builder, ['slug-internal' => $this->isInternalUser]);

        if (!$isNew) {

            $i18ns = new WidgetType\i18nsCollectionType($this->translator);
            $i18ns->add($builder, [
                'website' => $options['website'],
                'data_config' => true,
                'content_config' => false,
                'fields' => ['title' => 'col-md-6', 'subTitle' => 'col-md-6', 'body', 'placeholder' => 'col-12'],
                'label_fields' => [
                    'title' => $this->translator->trans("Objet e-mail de reception", [], 'admin'),
                    'subTitle' => $this->translator->trans("Objet e-mail de confirmation", [], 'admin'),
                    'body' => $this->translator->trans("Corps de l'e-mail de confirmation", [], 'admin'),
                    'placeholder' => $this->translator->trans('Message de remerciement sur le site', [], 'admin'),
                ],
                'placeholder_fields' => [
                    'title' => $this->translator->trans("Saisissez un objet", [], 'admin'),
                    'subTitle' => $this->translator->trans("Saisissez un objet", [], 'admin'),
                    'body' => $this->translator->trans("Saisissez un message", [], 'admin'),
                    'placeholder' => $this->translator->trans('Saisissez un message', [], 'admin'),
                ],
            ]);

            $builder->add('configuration', ConfigurationType::class, [
                'label' => false,
                'is_new' => $isNew,
                'entity' => $form->getConfiguration(),
                'excludes' => ['ajax'],
                'attr' => ['data-config' => true]
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
            'data_class' => StepForm::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}