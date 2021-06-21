<?php

namespace App\Controller\Security\Admin;

use App\Controller\Front\FrontController;
use App\Entity\Core\Website;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use App\Form\Manager\Security\Admin\RegisterManager;
use App\Form\Type\Security\Admin\RegistrationType;
use App\Repository\Core\WebsiteRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * SecurityController
 *
 * Main security controller to manage auth User
 *
 * @Route("/secure/user/{_locale}/%security_token%")
 *
 * @property string LOGIN_TYPE
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SecurityController extends FrontController
{
    private const LOGIN_TYPE = 'login'; // 'login' or 'email

    /**
     * Login page
     *
     * @Route("/login", methods={"GET", "POST"}, name="security_login", schemes={"%protocol%"})
     *
     * @param Request $request
     * @param AuthenticationUtils $authenticationUtils
     * @param WebsiteRepository $websiteRepository
     * @return RedirectResponse|Response
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils, WebsiteRepository $websiteRepository)
    {
        $website = $websiteRepository->findCurrent();
        if (!$website->getSecurity()->getSecurityKey()) {
            $this->setSecurityKey($website);
        }

        $session = $request->getSession();
        if ($session->get('PASSWORD_EXPIRE')) {
            $session->remove('PASSWORD_EXPIRE');
            return $this->redirect($this->generateUrl('security_password_request') . '?expire=true');
        }

        if (!empty($this->getUser()) && $this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin_dashboard', [
                'website' => $website->getId()
            ]);
        }

        if ($request->get('inactive')) {
            $session = new Session();
            $session->getFlashBag()->add('warning', $this->translator->trans("Votre compte n'est pas activé.", [], 'security_cms'));
        }

        return $this->cache('security/login.html.twig', NULL, [
            'website' => $this->getWebsite($request),
            'login_type' => $_ENV['SECURITY_ADMIN_LOGIN_TYPE'],
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    /**
     * Logout
     *
     * @Route("/logout", methods={"GET"}, name="security_logout", schemes={"%protocol%"})
     *
     * @throws Exception
     */
    public function logout()
    {
        throw new Exception('Will be intercepted before getting here');
    }

    /**
     * Register page
     *
     * @Route("/register", methods={"GET", "POST"}, name="security_register", schemes={"%protocol%"})
     *
     * @param Request $request
     * @param WebsiteRepository $websiteRepository
     * @param RegisterManager $manager
     * @return Response|null
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function register(
        Request $request,
        WebsiteRepository $websiteRepository,
        RegisterManager $manager)
    {
        $website = $websiteRepository->findOneByHost($request->getHost());
        $security = $website->getSecurity();

        if (!$security->getAdminRegistration()) {
            return $this->redirectToRoute('front_index');
        }

        $form = $this->createForm(RegistrationType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            return $manager->register($form->getData(), $security, $website);
        }

        if ($request->get('validation')) {
            $session = new Session();
            $session->getFlashBag()->add('success', $this->translator->trans("Merci pour inscription. Votre compte dois être validé par l'administrateur.", [], 'security_cms'));
        }

        return $this->cache('security/register.html.twig', NULL, [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/security/json", methods={"GET"}, name="security_json", schemes={"%protocol%"})
     *
     * @return JsonResponse
     */
    public function accountApi()
    {
        $user = $this->getUser();
        return $this->json($user, 200, [], [
            'groups' => 'main'
        ]);
    }

    /**
     * Set Website Security key
     *
     * @param Website $website
     * @throws Exception
     */
    private function setSecurityKey(Website $website)
    {
        $security = $website->getSecurity();
        $security->setSecurityKey(crypt(random_bytes(30), 'rl'));
        $this->entityManager->persist($security);
        $this->entityManager->flush();
        $this->entityManager->refresh($security);
    }
}