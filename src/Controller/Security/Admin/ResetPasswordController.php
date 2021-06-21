<?php

namespace App\Controller\Security\Admin;

use App\Controller\Admin\AdminController;
use App\Form\Manager\Security\Admin\ConfirmPasswordManager;
use App\Form\Manager\Security\Admin\ResetPasswordManager;
use App\Form\Type\Security\Admin\PasswordRequestType;
use App\Form\Type\Security\Admin\PasswordResetType;
use App\Repository\Security\UserRepository;
use App\Security\RecaptchaAuthenticator;
use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * ResetPasswordController
 *
 * Security reset password management
 *
 * @Route("/secure/user/reset-passowrd/{_locale}", schemes={"%protocol%"})
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ResetPasswordController extends AdminController
{
    /**
     * Request password
     *
     * @Route("/request", methods={"GET", "POST"}, name="security_password_request")
     *
     * @param Request $request
     * @param RecaptchaAuthenticator $recaptchaAuthenticator
     * @param ResetPasswordManager $manager
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function request(Request $request, RecaptchaAuthenticator $recaptchaAuthenticator, ResetPasswordManager $manager)
    {
        $form = $this->createForm(PasswordRequestType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $recaptchaAuthenticator->execute($request)) {
            $manager->send($form->getData());
            return $this->redirectToRoute('security_password_request');
        }

        if ($request->get('expire')) {
            $session = new Session();
            $session->getFlashBag()->add('warning', $this->translator->trans("Votre mot de passe a expiré, vous devez le réinitialiser.", [], 'security_cms'));
        }

        return $this->cache('security/password-request.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Reset password
     *
     * @Route("/confirm/{token}", methods={"GET", "POST"}, name="security_password_confirm")
     *
     * @param Request $request
     * @param string $token
     * @param UserRepository $repository
     * @param RecaptchaAuthenticator $recaptchaAuthenticator
     * @param ConfirmPasswordManager $manager
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function confirm(
        Request $request,
        string $token,
        UserRepository $repository,
        RecaptchaAuthenticator $recaptchaAuthenticator,
        ConfirmPasswordManager $manager,
        AuthorizationCheckerInterface $authorizationChecker)
    {
        $user = $repository->findOneByToken(urldecode($token));

        $form = $this->createForm(PasswordResetType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $recaptchaAuthenticator->execute($request)) {
            $manager->confirm($form->getData(), $user);
            $this->addFlash('success', $this->translator->trans("Votre mot de passe a été modifié avec succès.", [], 'security_cms'));
            return $this->redirectToRoute('security_login');
        }

        if (!$user && $authorizationChecker->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin_dashboard', ['website' => $this->getWebsite($request)->getId()]);
        } elseif (!$user) {
            throw $this->createAccessDeniedException($this->translator->trans('Accès refusé.', [], 'security_cms'));
        }

        return $this->cache('security/password-confirm.html.twig', [
            'form' => $form->createView()
        ]);
    }
}