<?php

namespace App\Form\Widget;

use App\Form\DataTransformer\EmailToUserTransformer;
use App\Repository\Security\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

/**
 * UserSelectTextType
 *
 * @property UserRepository $userRepository
 * @property RouterInterface $router
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class UserSelectTextType extends AbstractType
{
    private $userRepository;
    private $router;

    /**
     * UserSelectTextType constructor.
     *
     * @param UserRepository $userRepository
     * @param RouterInterface $router
     */
    public function __construct(UserRepository $userRepository, RouterInterface $router)
    {
        $this->userRepository = $userRepository;
        $this->router = $router;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new EmailToUserTransformer(
            $this->userRepository,
            $options['finder_callback']
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attr' => [
                'class' => 'js-autocomplete',
                'data-autocomplete-url' => $this->router->generate('admin_security_utility'),
                'data-autocomplete-key' => 'email',
            ],
            'invalid_message' => "Cet utilisateur n'existe pas",
            'finder_callback' => function (UserRepository $userRepository, string $email) {
                return $userRepository->findOneBy(['email' => $email]);
            }
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $attr = $view->vars['attr'];
        $class = isset($attr['class']) ? $attr['class'] . ' ' : '';
        $class .= 'js-autocomplete';

        $attr['class'] = $class;
        $attr['data-autocomplete-url'] = $this->router->generate('admin_security_utility');
        $attr['data-autocomplete-key'] = 'email';

        $view->vars['attr'] = $attr;
    }

    /**
     * {@inheritDoc}
     */
    public function getParent(): ?string
    {
        return TextType::class;
    }
}