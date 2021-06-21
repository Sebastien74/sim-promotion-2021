<?php

namespace App\Form\Type\Media;

use App\Entity\Core\Website;
use App\Entity\Media\Folder;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * SelectFolderType
 *
 * @property Request $request
 * @property TranslatorInterface $translator
 * @property bool $isInternalUser
 * @property Website $website
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SelectFolderType extends AbstractType
{
    private $request;
    private $translator;
    private $isInternalUser;
    private $website;

    /**
     * SelectFolderType constructor.
     *
     * @param RequestStack $requestStack
     * @param TranslatorInterface $translator
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(RequestStack $requestStack, TranslatorInterface $translator, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->translator = $translator;
        $this->isInternalUser = $authorizationChecker->isGranted('ROLE_INTERNAL');
        $this->website = $this->request->get('website');
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('folder', EntityType::class, [
            'label' => false,
            'required' => false,
            'display' => 'search',
            'placeholder' => $this->translator->trans('Racine', [], 'admin'),
            'class' => Folder::class,
            'attr' => [
                'class' => 'folder-selector',
                'group' => 'p-0'
            ],
            'query_builder' => function (EntityRepository $er) {
                if ($this->isInternalUser) {
                    return $er->createQueryBuilder('f')
                        ->where('f.website = :website')
                        ->setParameter(':website', $this->website)
                        ->orderBy('f.adminName', 'ASC');
                } else {
                    return $er->createQueryBuilder('f')
                        ->andWhere('f.website = :website')
                        ->andWhere('f.webmaster = :webmaster')
                        ->setParameter(':website', $this->website)
                        ->setParameter(':webmaster', false)
                        ->orderBy('f.adminName', 'ASC');
                }
            },
            'choice_label' => function ($entity) {
                return strip_tags($entity->getAdminName());
            }
        ]);

        $builder->add('save', Type\SubmitType::class, [
            'label' => $this->translator->trans('Déplacer', [], 'admin'),
            'attr' => ['class' => 'btn-info disable-preloader']
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => NULL,
            'website' => NULL,
            'translation_domain' => 'admin'
        ]);
    }
}