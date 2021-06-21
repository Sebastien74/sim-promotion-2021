<?php

namespace App\Security;

use App\Entity\Core\Website;
use App\Entity\Security\User;
use App\Repository\Security\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
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
 * LoginFormAuthenticator
 *
 * @property string $LOGIN_TYPE
 * @property string LOGIN_ROUTE
 *
 * @property string REGISTER_ROUTE
 *
 * @property UserRepository $userRepository
 * @property UrlGeneratorInterface $urlGenerator
 * @property CsrfTokenManagerInterface $csrfTokenManager
 * @property UserPasswordEncoderInterface $passwordEncoder
 * @property RecaptchaAuthenticator $recaptchaAuthenticator
 * @property EntityManagerInterface $entityManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class LoginFormAuthenticator extends AbstractFormLoginAuthenticator implements PasswordAuthenticatedInterface
{
    use TargetPathTrait;

    private $LOGIN_TYPE = 'login'; /** $_ENV['SECURITY_ADMIN_LOGIN_TYPE'] */

    private const LOGIN_ROUTE = 'security_login';
    private const REGISTER_ROUTE = 'security_register';

    private $userRepository;
    private $urlGenerator;
    private $csrfTokenManager;
    private $passwordEncoder;
    private $recaptchaAuthenticator;
    private $entityManager;

    /**
     * LoginFormAuthenticator constructor.
     *
     * @param UserRepository $userRepository
     * @param UrlGeneratorInterface $urlGenerator
     * @param CsrfTokenManagerInterface $csrfTokenManager
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param RecaptchaAuthenticator $recaptchaAuthenticator
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        UserRepository $userRepository,
        UrlGeneratorInterface $urlGenerator,
        CsrfTokenManagerInterface $csrfTokenManager,
        UserPasswordEncoderInterface $passwordEncoder,
        RecaptchaAuthenticator $recaptchaAuthenticator,
        EntityManagerInterface $entityManager)
    {
        $this->userRepository = $userRepository;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->recaptchaAuthenticator = $recaptchaAuthenticator;
        $this->entityManager = $entityManager;

        $this->LOGIN_TYPE = $_ENV['SECURITY_ADMIN_LOGIN_TYPE'];
    }

    /**
     * @param Request $request
     * @param AuthenticationException|null $authException
     * @return RedirectResponse
     */
    public function start(Request $request, AuthenticationException $authException = null): RedirectResponse
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
    public function supports(Request $request): bool
    {
        if ($request->get('_route') === self::LOGIN_ROUTE
            && $request->isMethod('POST')
            && !$this->recaptchaAuthenticator->execute($request)) {
            return false;
        }

        if ($request->get('_route') === self::LOGIN_ROUTE
            && $request->isMethod('POST')
            && $request->request->get($this->LOGIN_TYPE)) {
            $user = $this->entityManager->getRepository(User::class)->findOneBy([$this->LOGIN_TYPE => $request->request->get($this->LOGIN_TYPE)]);
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
    public function getCredentials(Request $request): array
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
     * Get User
     *
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     * @return User|UserInterface|null
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        return $this->userRepository->findOneBy([$this->LOGIN_TYPE => $credentials[$this->LOGIN_TYPE]]);
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

        /** @var Website $website */
        $website = $this->entityManager->getRepository(Website::class)->findCurrent();
        /** @var User $user */
        $user = $token->getUser();
        $groupRedirection = $user->getGroup()->getLoginRedirection();
        $routeRedirection = $groupRedirection ? $groupRedirection : 'admin_dashboard';

        $this->clearAdminSession();

        return new RedirectResponse($this->urlGenerator->generate($routeRedirection, [
            'website' => $website->getId()
        ]));
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
     * Clear admin session
     */
    private function clearAdminSession()
    {
        $sessionRequest = new Session();
        foreach ($sessionRequest->all() as $name => $value) {
            if (preg_match('/configuration_/', $name) || $name === 'adminWebsite') {
                $sessionRequest->remove($name);
            }
        }
    }

    /**
     * Get login URL
     *
     * @return string
     */
    protected function getLoginUrl(): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
