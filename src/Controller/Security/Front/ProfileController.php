<?php

namespace App\Controller\Security\Front;

use App\Controller\Front\FrontController;
use App\Entity\Security\UserFront;
use App\Form\Manager\Security\Front\ProfileManager;
use App\Form\Type\Security\Front\UserFrontType;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * ProfileController
 *
 * FrontUser Profile management
 *
 * @IsGranted("ROLE_SECURE_PAGE")
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ProfileController extends FrontController
{
    /**
     * Show UserFront Profile
     *
     * @Route({
     *     "fr": "/espace-personnel/mon-profil",
     *     "en": "/customer-area/my-profile"
     * }, name="security_front_profile", methods={"GET"}, schemes={"%protocol%"})
     *
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function show(Request $request)
    {
        $user = $this->getUser();

        if (!$user instanceof UserFront) {
            return $this->redirectToRoute('security_front_login');
        }

        $website = $this->getWebsite($request);
        $websiteTemplate = $website->getConfiguration()->getTemplate();
        $this->synchronize($user);

        return $this->cache('front/' . $websiteTemplate . '/actions/security/profile-show.html.twig', NULL, [
            'website' => $website,
            'websiteTemplate' => $websiteTemplate,
            'user' => $user
        ]);
    }

    /**
     * Edit UserFront Profile
     *
     * @Route({
     *     "fr": "/espace-personnel/mon-profil/edition",
     *     "en": "/customer-area/my-profile/edit"
     * }, name="security_front_profile_edit", methods={"GET", "POST"}, schemes={"%protocol%"})
     *
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function edit(Request $request)
    {
        $user = $this->getUser();

        if (!$user instanceof UserFront) {
            return $this->redirectToRoute('security_front_login');
        }

        $website = $this->getWebsite($request);
        $websiteTemplate = $website->getConfiguration()->getTemplate();
        $this->synchronize($user);

        $form = $this->createForm(UserFrontType::class, $this->getUser());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->subscriber->get(ProfileManager::class);
            $manager->execute($form);
            return $this->redirectToRoute('security_front_profile');
        }

        return $this->cache('front/' . $websiteTemplate . '/actions/security/profile-edit.html.twig', NULL, [
            'website' => $website,
            'websiteTemplate' => $websiteTemplate,
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * Synchronize UserFront data
     *
     * @param UserFront $user
     */
    private function synchronize(UserFront $user)
    {
        $manager = $this->subscriber->get(ProfileManager::class);
        $manager->synchronize($user);
    }
}