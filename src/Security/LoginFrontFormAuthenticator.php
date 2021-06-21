<?php

namespace App\Security;

use App\Entity\Core\Website;
use App\Entity\Security\UserFront;
use App\Repository\Security\UserFrontRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Guard\PasswordAuthenticatedInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

/**
 * LoginFrontFormAuthenticator
 *
 * @property string $LOGIN_TYPE
 * @property string LOGIN_ROUTE
 * @property string REGISTER_ROUTE
 * @property UserFrontRepository $userRepository
 * @property UrlGeneratorInterface $urlGenerator
 * @property CsrfTokenManagerInterface $csrfTokenManager
 * @property UserPasswordEncoderInterface $passwordEncoder
 * @property Request $request
 * @property RecaptchaAuthenticator $recaptchaAuthenticator
 * @property EntityManagerInterface $entityManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class LoginFrontFormAuthenticator extends AbstractFormLoginAuthenticator implements PasswordAuthenticatedInterface
{
    use TargetPathTrait;

    private $LOGIN_TYPE = 'login'; // $_ENV['SECURITY_FRONT_LOGIN_TYPE']

    private const LOGIN_ROUTE = 'security_front_login';
    private const REGISTER_ROUTE = 'security_front_register';

    private $userRepository;
    private $urlGenerator;
    private $csrfTokenManager;
    private $passwordEncoder;
    private $request;
    private $requestStack;
    private $recaptchaAuthenticator;
    private $entityManager;

    /**
     * LoginFrontFormAuthenticator constructor.
     *
     * @param UserFrontRepository $userRepository
     * @param UrlGeneratorInterface $urlGenerator
     * @param CsrfTokenManagerInterface $csrfTokenManager
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param RequestStack $requestStack
     * @param RecaptchaAuthenticator $recaptchaAuthenticator
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        UserFrontRepository $userRepository,
        UrlGeneratorInterface $urlGenerator,
        CsrfTokenManagerInterface $csrfTokenManager,
        UserPasswordEncoderInterface $passwordEncoder,
        RequestStack $requestStack,
        RecaptchaAuthenticator $recaptchaAuthenticator,
        EntityManagerInterface $entityManager)
    {
        $this->userRepository = $userRepository;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->request = $requestStack->getMasterRequest();
        $this->recaptchaAuthenticator = $recaptchaAuthenticator;
        $this->entityManager = $entityManager;

        $this->LOGIN_TYPE = $_ENV['SECURITY_FRONT_LOGIN_TYPE'];
    }

    /**
     * @param Request $request
     * @param AuthenticationException|null $authException
     * @return RedirectResponse
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        if (is_object($authException) && !$request->getUser() && $authException->getPrevious()->getCode() === 403) {
            return new RedirectResponse($this->urlGenerator->generate('front_index'));
        }

        return parent::start($request, $authException);
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function supports(Request $request)
    {
        if ($request->get('_route') === self::LOGIN_ROUTE
            && $request->isMethod('POST')
            && !$this->recaptchaAuthenticator->execute($request)) {
            return false;
        }

        if ($request->get('_route') === self::LOGIN_ROUTE
            && $request->isMethod('POST')
            && $request->request->get($this->LOGIN_TYPE)) {
            $user = $this->entityManager->getRepository(UserFront::class)->findOneBy([$this->LOGIN_TYPE => $request->request->get($this->LOGIN_TYPE)]);
            if ($user && $user->getResetPassword()) {
                $request->getSession()->set('PASSWORD_EXPIRE', true);
            }
        }

        return $request->attributes->get('_route') === self::LOGIN_ROUTE
            && $request->isMethod('POST');
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials(Request $request)
    {
        $credentials = [
            $this->LOGIN_TYPE => $request->request->get($this->LOGIN_TYPE),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];

        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials[$this->LOGIN_TYPE]
        );

        return $credentials;
    }

    /**
     * Get UserFront
     *
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     * @return UserFront|UserInterface|null
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        $website = $this->entityManager->getRepository(Website::class)->findOneByHost($this->request->getHost(), true);

        if (!$website) {
            $session = new Session();
            $session->getFlashBag()->add('error', 'Veuillez ajouter le nom de domaine dans votre configuration');
            return NULL;
        }

        return $this->userRepository->findOneBy([$this->LOGIN_TYPE => $credentials[$this->LOGIN_TYPE], 'website' => $website]);
    }

    /**
     * Check Credentials
     *
     * @param mixed $credentials
     * @param UserInterface $user
     * @return bool|RedirectResponse
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     *
     * @param $credentials
     * @return string|null
     */
    public function getPassword($credentials): ?string
    {
        return $credentials['password'];
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        if ($request->get('_route') === self::REGISTER_ROUTE) {
            return new RedirectResponse($this->urlGenerator->generate(self::REGISTER_ROUTE));
        }

        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new RedirectResponse($targetPath);
        }

        $previousUrl = $request->getSession()->get('previous_secure_url');

        if ($previousUrl && !preg_match('/validation=/', $previousUrl)) {
            $request->getSession()->remove('previous_secure_url');
            return new RedirectResponse($previousUrl);
        } else {
            return new RedirectResponse($this->urlGenerator->generate('front_index'));
        }
    }

    /**
     * Override to change what happens after a bad username/password is submitted.
     *
     * @param Request $request
     * @param AuthenticationException $exception
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if ($request->hasSession()) {
            $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);
        }
    }

    /**
     * Get login URL
     *
     * @return string
     */
    protected function getLoginUrl(): string
    {
        if ($this->request->get('tpl-form')) {
            return $this->request->headers->get('referer');
        }

        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}