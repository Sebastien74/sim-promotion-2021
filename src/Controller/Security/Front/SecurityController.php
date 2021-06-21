<?php

namespace App\Controller\Security\Front;

use App\Controller\Front\FrontController;
use App\Entity\Core\Configuration;
use App\Entity\Core\Module;
use App\Entity\Security\UserCategory;
use App\Entity\Security\UserFront;
use App\Form\Manager\Security\Front\RegisterManager;
use App\Form\Type\Security\Front\RegistrationType;
use App\Repository\Core\WebsiteRepository;
use App\Repository\Security\UserFrontRepository;
use App\Security\RecaptchaAuthenticator;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * SecurityController
 *
 * Front User security controller to manage auth User
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SecurityController extends FrontController
{
    /**
     * Login page
     *
     * @Route({
     *     "fr": "/espace-personnel/connexion",
     *     "en": "/customer-area/login",
     *     "es": "/espacio-de-clientes/accesso",
     *     "it": "/spazio-cliente/accesso"
     * }, name="security_front_login", methods={"GET", "POST"}, schemes={"%protocol%"})
     *
     * @Cache(expires="tomorrow", public=true)
     *
     * @param Request $request
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     * @throws Exception
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils)
    {
        $website = $this->getWebsite($request, true);
        $websiteTemplate = $website->getConfiguration()->getTemplate();
        $template = $request->get('tpl-form') ? 'login-form' : 'login';
        $secureModuleRole = $request->get('tpl-form') ? 'ROLE_SECURE_MODULE' : 'ROLE_SECURE_PAGE';
        $secureModule = $this->entityManager->getRepository(Module::class)->findOneBy(['role' => $secureModuleRole]);
        $secureActive = $secureModule instanceof Module ? $this->entityManager->getRepository(Configuration::class)->moduleExist($website, $secureModule) : NULL;

        $session = $request->getSession();
        if ($session->get('PASSWORD_EXPIRE')) {
            $session->remove('PASSWORD_EXPIRE');
            return $this->redirect($this->generateUrl('security_front_password_request') . '?expire=true');
        }

        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->cache('front/' . $websiteTemplate . '/actions/security/user-switcher.html.twig', NULL, [
                'templateName' => 'front-user-switcher',
                'websiteTemplate' => $websiteTemplate,
                'users' => $this->entityManager->getRepository(UserFront::class)->findAll()
            ]);
        }

        if ($request->get('inactive')) {
            $session = new Session();
            $session->getFlashBag()->add('warning', $this->translator->trans("Votre compte n'est pas activé.", [], 'security_cms'));
        }

        return $this->cache('front/' . $websiteTemplate . '/actions/security/' . $template . '.html.twig', NULL, [
            'templateName' => 'front-login',
            'website' => $website,
            'secureActive' => $secureActive,
            'login_type' => $_ENV['SECURITY_FRONT_LOGIN_TYPE'],
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'websiteTemplate' => $websiteTemplate
        ]);
    }

    /**
     * Register
     *
     * @Route({
     *     "fr": "/espace-personnel/inscription/{category}",
     *     "en": "/customer-area/sign-up/{category}",
     *     "es": "/espacio-de-clientes/inscribirse/{category}",
     *     "it": "/spazio-cliente/registro/{category}"
     * }, name="security_front_register", defaults={"category"=NULL}, methods={"GET", "POST"}, schemes={"%protocol%"})
     *
     * @param Request $request
     * @param WebsiteRepository $websiteRepository
     * @param RegisterManager $manager
     * @param RecaptchaAuthenticator $recaptchaAuthenticator
     * @param string|null $category
     * @return Response|null
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function register(
        Request $request,
        WebsiteRepository $websiteRepository,
        RegisterManager $manager,
        RecaptchaAuthenticator $recaptchaAuthenticator,
        string $category = NULL)
    {
        $website = $websiteRepository->findOneByHost($request->getHost());
        $this->entityManager->refresh($website);
        $template = $website->getConfiguration()->getTemplate();
        $security = $website->getSecurity();
        /** @var UserCategory|null $userCategory */
        $userCategory = $this->entityManager->getRepository(UserCategory::class)->findBySlug($request->getLocale(), $category);

        if (!$security->getFrontRegistration()) {
            return $this->redirectToRoute('front_index');
        }

        $form = $this->createForm(RegistrationType::class, NULL, [
            'action' => $this->generateUrl('security_front_register', ['category' => $category]),
            'website' => $website
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && $recaptchaAuthenticator->execute($request)) {
            return $manager->register($form->getData(), $security, $website, $userCategory);
        }

        if ($request->get('validation')) {

            $session = new Session();
            $message = $security->getFrontRegistrationValidation()
                ? $this->translator->trans("Merci pour votre inscription. Votre compte dois être validé par l'administrateur.", [], 'security_cms')
                : $this->translator->trans("Merci pour votre inscription. Un e-mail de confirmation vous a été envoyé. Vous pouvez vous connecter.", [], 'security_cms');

            $session->getFlashBag()->add('success', $message);

            if (!$security->getFrontRegistrationValidation()) {
                return $this->redirectToRoute('security_front_login');
            }
        }

        return $this->cache('front/' . $template . '/actions/security/register.html.twig', NULL, [
            'templateName' => 'front-user-register',
            'websiteTemplate' => $template,
            'category' => $userCategory,
            'form' => $form->createView()
        ]);
    }

    /**
     * Email confirmation page
     *
     * @Route({
     *     "fr": "/espace-personnel/email/confirmation/{token}",
     * }, name="security_front_email_confirmation", methods={"GET"}, schemes={"%protocol%"})
     *
     * @param Request $request
     * @param string $token
     * @param UserFrontRepository $userFrontRepository
     * @return Response
     * @throws Exception
     */
    public function confirmEmail(Request $request, string $token, UserFrontRepository $userFrontRepository)
    {
        $website = $this->getWebsite($request);
        $websiteTemplate = $website->getConfiguration()->getTemplate();
        $security = $website->getSecurity();
        $token = urldecode($token);
        $user = $token ? $userFrontRepository->findOneBy(['token' => $token]) : NULL;

        if (!$security->getFrontRegistration() || !$user) {
            return $this->redirectToRoute('front_index');
        }

        $user->setConfirmEmail(true);
        $user->setToken(NULL);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->cache('front/' . $websiteTemplate . '/actions/security/email-confirmation.html.twig', NULL, [
            'websiteTemplate' => $websiteTemplate
        ]);
    }
}