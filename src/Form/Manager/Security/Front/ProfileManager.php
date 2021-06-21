<?php

namespace App\Form\Manager\Security\Front;

use App\Entity\Information\Address;
use App\Entity\Security\Profile;
use App\Entity\Security\UserFront;
use App\Form\Manager\Security\PictureManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * ProfileManager
 *
 * Manage UserFront Profile
 *
 * @property EntityManagerInterface $entityManager
 * @property PictureManager $pictureManager
 * @property TranslatorInterface $translator
 * @property string $addressConfiguration
 * @property Profile $profile
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ProfileManager
{
    private $entityManager;
    private $pictureManager;
    private $translator;
    private $addressConfiguration;
    private $profile;

    /**
     * ProfileManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param PictureManager $pictureManager
     * @param TranslatorInterface $translator
     */
    public function __construct(EntityManagerInterface $entityManager, PictureManager $pictureManager, TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->pictureManager = $pictureManager;
        $this->translator = $translator;
        $this->addressConfiguration = $_ENV['SECURITY_FRONT_ADDRESSES'];
    }

    /**
     * To synchronize all UserFront data
     *
     * @param UserFront $user
     */
    public function synchronize(UserFront $user)
    {
        $this->synchronizeProfile($user);
        $this->synchronizeAddresses($user);
    }

    /**
     * To execute process after POST
     *
     * @param Form $form
     */
    public function execute(Form $form)
    {
        $user = $form->getData();

        $this->pictureManager->execute($user, $form);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $session = new Session();
        $session->getFlashBag()->add('success', $this->translator->trans('Enregistré avec succès.', [], 'front'));
    }

    /**
     * To synchronize Addresses
     *
     * @param UserFront $user
     */
    private function synchronizeProfile(UserFront $user)
    {
        if (!$user->getProfile() instanceof Profile) {
            $profile = new Profile();
            $profile->setUserFront($user);
            $user->setProfile($profile);
        }

        $this->profile = $user->getProfile();
    }

    /**
     * To synchronize Addresses
     *
     * @param UserFront $user
     */
    private function synchronizeAddresses(UserFront $user)
    {
        if ($this->addressConfiguration) {
            $this->synchronizeAddress($user, 'basic', 1);
            if ($this->addressConfiguration === 'full') {
                $this->synchronizeAddress($user, 'billing', 2);
            }
        }
    }

    /**
     * To synchronize Address
     *
     * @param UserFront $user
     * @param string $slug
     * @param int $position
     */
    private function synchronizeAddress(UserFront $user, string $slug, int $position)
    {
        if ($this->addressConfiguration) {

            $repository = $this->entityManager->getRepository(Profile::class);
            $existing = $repository->addressExist($user, $slug);

            if (!$existing) {

                $address = new Address();
                $address->setPosition($position);
                $address->setSlug($slug);
                $this->profile->addAddress($address);

                $this->entityManager->persist($this->profile);
                $this->entityManager->flush();
            }
        }
    }
}