<?php

namespace App\Form\Type\Module\Contact;

use App\Entity\Module\Contact\Contact;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * ContactType
 *
 * @property TranslatorInterface $translator
 * @property bool $isInternalUser
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ContactType extends AbstractType
{
    private $translator;
    private $isInternalUser;

    /**
     * ContactType constructor.
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
        $isNew = !$builder->getData()->getId();

        $adminName = new WidgetType\AdminNameType($this->translator);
        $adminName->add($builder, ['slug-internal' => $this->isInternalUser]);

        if (!$isNew) {
            $i18ns = new WidgetType\i18nsCollectionType($this->translator);
            $i18ns->add($builder, [
                'fields' => ['title', 'body', 'targetPage' => 'col-md-4', 'targetLabel' => 'col-md-4', 'targetLink' => 'col-md-4', 'placeholder', 'help'],
                'label_fields' => [
                    'targetPage' => $this->translator->trans("Page de contact", [], 'admin'),
                    'targetLabel' => $this->translator->trans("Intitulé page de la contact", [], 'admin'),
                    'targetLink' => $this->translator->trans("E-mail", [], 'admin'),
                    'placeholder' => $this->translator->trans("Numéro de téléphone", [], 'admin'),
                    'help' => $this->translator->trans("Numéro de téléphone (href)", [], 'admin')
                ],
                'placeholder_fields' => [
                    'targetLabel' => $this->translator->trans("Saisissez un intitulé", [], 'admin'),
                    'targetLink' => $this->translator->trans("Saisissez un e-mail", [], 'admin'),
                    'placeholder' => $this->translator->trans("Saisissez un numéro", [], 'admin'),
                    'help' => $this->translator->trans("Saisissez un numéro", [], 'admin')
                ],
                'excludes_fields' => ['headerTable', 'targetStyle', 'newTab', 'externalLink'],
                'content_config' => false
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
            'data_class' => Contact::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}