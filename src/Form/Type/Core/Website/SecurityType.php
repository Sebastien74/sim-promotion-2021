<?php

namespace App\Form\Type\Core\Website;

use App\Entity\Core\Module;
use App\Entity\Core\Configuration as CoreConfiguration;
use App\Entity\Core\Security;
use App\Entity\Core\Website;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * SecurityType
 *
 * @property TranslatorInterface $translator
 * @property EntityManagerInterface $entityManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SecurityType extends AbstractType
{
    private $translator;
    private $entityManager;

    /**
     * SecurityType constructor.
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
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('secureWebsite', Type\CheckboxType::class, [
            'label' => $this->translator->trans('Site sécurisé', [], 'admin'),
            'attr' => ['group' => 'col-md-3'],
            'display' => 'switch'
        ]);

        $builder->add('adminRegistration', Type\CheckboxType::class, [
            'label' => $this->translator->trans("Activer l'inscription ?", [], 'admin'),
            'attr' => ['group' => 'col-md-2 my-md-auto'],
            'display' => 'switch'
        ]);

        $builder->add('adminRegistrationValidation', Type\CheckboxType::class, [
            'label' => $this->translator->trans("Activer la validation à l'inscription ?", [], 'admin'),
            'attr' => ['group' => 'col-md-3 my-md-auto'],
            'display' => 'switch'
        ]);

        $builder->add('adminPasswordSecurity', Type\CheckboxType::class, [
            'label' => $this->translator->trans("Activer la validaté des mots de passe", [], 'admin'),
            'attr' => ['group' => 'col-md-3 my-md-auto'],
            'display' => 'switch'
        ]);

        $builder->add('adminPasswordDelay', Type\IntegerType::class, [
            'label' => $this->translator->trans('Validité des mots de passe (nbr jours)', [], 'admin'),
            'attr' => [
                'group' => 'col-md-3',
                'placeholder' => $this->translator->trans("Saisissez une durée", [], 'admin')
            ]
        ]);

        $secureModule = $this->getSecureModule($options['website']);

        if ($secureModule) {

            $builder->add('frontRegistration', Type\CheckboxType::class, [
                'label' => $this->translator->trans("Activer l'inscription ?", [], 'admin'),
                'attr' => ['group' => 'col-md-2'],
                'display' => 'switch'
            ]);

            $builder->add('frontRegistrationValidation', Type\CheckboxType::class, [
                'label' => $this->translator->trans("Activer la validation à l'inscription ?", [], 'admin'),
                'attr' => ['group' => 'col-md-3'],
                'display' => 'switch'
            ]);

            $builder->add('frontEmailConfirmation', Type\CheckboxType::class, [
                'label' => $this->translator->trans("Activer la confirmation de l'e-mail ?", [], 'admin'),
                'attr' => ['group' => 'col-md-3'],
                'display' => 'switch'
            ]);

            $builder->add('frontPasswordSecurity', Type\CheckboxType::class, [
                'label' => $this->translator->trans("Activer la validaté des mots de passe", [], 'admin'),
                'attr' => ['group' => 'col-md-3'],
                'display' => 'switch'
            ]);

            $builder->add('frontPasswordDelay', Type\IntegerType::class, [
                'label' => $this->translator->trans('Validité des mots de passe (nbr jours)', [], 'admin'),
                'attr' => [
                    'group' => 'col-md-3',
                    'placeholder' => $this->translator->trans("Saisissez une durée", [], 'admin')
                ]
            ]);
        }
    }

    /**
     * Check if secure pages Module is activated
     *
     * @param Website $website
     * @return bool
     */
    private function getSecureModule(Website $website)
    {
        foreach ($website->getConfiguration()->getModules() as $module) {
            if ($module->getSlug() === 'secure-page') {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Security::class,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}