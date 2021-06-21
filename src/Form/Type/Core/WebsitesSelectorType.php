<?php

namespace App\Form\Type\Core;

use App\Entity\Core\Website;
use App\Entity\Security\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * WebsitesSelectorType
 *
 * @property EntityManagerInterface $entityManager
 * @property bool $isInternalUser
 * @property User|null $user
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class WebsitesSelectorType extends AbstractType
{
    private $entityManager;
    private $isInternalUser;
    private $user;

    /**
     * WebsitesSelectorType constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param KernelInterface $kernel
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(EntityManagerInterface $entityManager, KernelInterface $kernel, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->entityManager = $entityManager;
        $this->isInternalUser = $authorizationChecker->isGranted('ROLE_INTERNAL');
        $container = $kernel->getContainer();
        $token = $container->get('security.token_storage')->getToken();

        if (method_exists($token, 'getUser') && method_exists($token->getUser(), 'getId')) {
            $this->user = $token->getUser();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $website = $options['website'];

        $builder->add('websites', ChoiceType::class, [
            'label' => false,
            'display' => 'search',
            'attr' => ['class' => 'websites-selector', 'group' => 'col-12 mb-0 mt-2'],
            'data' => $website->getId(),
            'choices' => $this->getUserWebsites()
        ]);
    }

    /**
     * Get user Websites
     *
     * @return array
     */
    private function getUserWebsites()
    {
        $choices = [];
        $websites = $this->isInternalUser ?
            $this->entityManager->getRepository(Website::class)->findBy(['active' => true])
            : $this->user->getWebsites();

        foreach ($websites as $website) {
            if ($website->getActive()) {
                $choices[$website->getAdminName()] = $website->getId();
            }
        }

        return $choices;
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