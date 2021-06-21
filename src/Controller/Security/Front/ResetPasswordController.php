<?php

namespace App\Controller\Security\Front;

use App\Controller\Front\FrontController;
use App\Form\Manager\Security\Front\ConfirmPasswordManager;
use App\Form\Manager\Security\Front\ResetPasswordManager;
use App\Form\Type\Security\Front\PasswordRequestType;
use App\Form\Type\Security\Front\PasswordResetType;
use App\Repository\Security\UserFrontRepository;
use App\Security\RecaptchaAuthenticator;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
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
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ResetPasswordController extends FrontController
{
    /**
     * Request password
     *
     * @Route({
     *     "fr": "/espace-client/modification-mot-de-passe",
     *     "en": "/customer-area/password-change",
     *     "es": "/espacio-de-clientes/cambio-de-contrasena",
     *     "it": "/spazio-cliente/cambio-password"
     * }, name="security_front_password_request", methods={"GET", "POST"}, schemes={"%protocol%"})
     *
     * @Cache(expires="tomorrow", public=true)
     *
     * @param Request $request
     * @param ResetPasswordManager $manager
     * @param RecaptchaAuthenticator $recaptchaAuthenticator
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function request(Request $request, ResetPasswordManager $manager, RecaptchaAuthenticator $recaptchaAuthenticator)
    {
        $website = $this->getWebsite($request);
        $template = $website->getConfiguration()->getTemplate();
        $form = $this->createForm(PasswordRequestType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $recaptchaAuthenticator->execute($request)) {
            $manager->send($form->getData(), $website);
            return $this->redirectToRoute('security_front_password_request');
        }

        if ($request->get('expire')) {
            $session = new Session();
            $session->getFlashBag()->add('warning', $this->translator->trans("Votre mot de passe a expiré, vous devez le réinitialiser.", [], 'security_cms'));
        }

        return $this->cache('front/' . $template . '/actions/security/password-request.html.twig', NULL, [
            'templateName' => 'front-password-request',
            'websiteTemplate' => $template,
            'form' => $form->createView()
        ]);
    }

    /**
     * Reset password
     *
     * @Route({
     *     "fr": "/espace-client/mot-de-passe/reinitialisation/{token}",
     *     "en": "/customer-area/password/reset/{token}",
     *     "es": "/espacio-de-clientes/contrasena/reajustar/{token}",
     *     "it": "/spazio-cliente/password/reset/{token}"
     * }, name="security_front_password_confirm", methods={"GET", "POST"}, schemes={"%protocol%"})
     *
     * @param Request $request
     * @param string $token
     * @param UserFrontRepository $repository
     * @param ConfirmPasswordManager $manager
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param RecaptchaAuthenticator $recaptchaAuthenticator
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function confirm(
        Request $request,
        string $token,
        UserFrontRepository $repository,
        ConfirmPasswordManager $manager,
        AuthorizationCheckerInterface $authorizationChecker,
        RecaptchaAuthenticator $recaptchaAuthenticator)
    {
        $user = $repository->findOneByToken(urldecode($token));
        $website = $this->getWebsite($request);
        $template = $website->getConfiguration()->getTemplate();

        $form = $this->createForm(PasswordResetType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $recaptchaAuthenticator->execute($request)) {
            $manager->confirm($form->getData(), $user);
            $this->addFlash('success', $this->translator->trans("Votre mot de passe a été modifié avec succès.", [], 'security_cms'));
            return $this->redirectToRoute('security_front_login');
        }

        if (!$user && $authorizationChecker->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('front_index');
        } elseif (!$user) {
            throw $this->createAccessDeniedException($this->translator->trans('Accès refusé.', [], 'security_cms'));
        }

        return $this->cache('front/' . $template . '/actions/security/password-confirm.html.twig', NULL, [
            'templateName' => 'front-password-request',
            'websiteTemplate' => $template,
            'form' => $form->createView()
        ]);
    }
}