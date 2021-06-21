<?php

namespace App\Service\DataFixtures;

use App\Entity\Core\Website;
use App\Entity\Information\Address;
use App\Entity\Information\Email;
use App\Entity\Information\Information;
use App\Entity\Information\Legal;
use App\Entity\Information\Phone;
use App\Entity\Information\SocialNetwork;
use App\Entity\Security\User;
use App\Entity\Translation\i18n;
use Doctrine\ORM\EntityManagerInterface;

/**
 * InformationFixture
 *
 * Information Fixture management
 *
 * @property array ZONES
 *
 * @property EntityManagerInterface $entityManager
 * @property array $yamlConfiguration
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class InformationFixture
{
    private const ZONES = ['contact', 'footer'];

    private $entityManager;
    private $yamlConfiguration = [];

    /**
     * InformationFixture constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Add Information
     *
     * @param Website $website
     * @param array $yamlConfiguration
     * @param User|null $user
     * @return Information
     */
    public function add(Website $website, array $yamlConfiguration, User $user = NULL): Information
    {
        $this->yamlConfiguration = $yamlConfiguration;

        $configuration = $website->getConfiguration();
        $allLocales = $configuration->getAllLocales();
        $defaultLocale = $website->getConfiguration()->getLocale();

        $information = new Information();
        $information->setWebsite($website);

        if ($user) {
            $website->setCreatedBy($user);
        }

        $website->setInformation($information);

        foreach ($allLocales as $locale) {
            $this->addSocialNetworks($information, $locale, $defaultLocale);
            $this->addPhones($information, $locale, $defaultLocale);
            $this->addEmails($information, $locale, $defaultLocale);
            $this->addAddresses($information, $locale, $defaultLocale);
            $this->addLegacy($information, $locale, $defaultLocale);
            $this->addI18n($information, $locale);
        }

        $this->entityManager->persist($information);
        $this->entityManager->persist($website);

        return $information;
    }

    /**
     * Add social networks
     *
     * @param Information $information
     * @param string $locale
     * @param string $defaultLocale
     */
    private function addSocialNetworks(Information $information, string $locale, string $defaultLocale)
    {
        $socialNetwork = new SocialNetwork();
        $socialNetwork->setLocale($locale);

        $socialNetworks = !empty($this->yamlConfiguration['social_networks'][$locale])
            ? $this->yamlConfiguration['social_networks'][$locale] : (!empty($this->yamlConfiguration['social_networks'][$defaultLocale])
                ? $this->yamlConfiguration['social_networks'][$defaultLocale] : []);

        foreach ($socialNetworks as $name => $url) {
            $setter = 'set' . ucfirst($name);
            $socialNetwork->$setter($url);
        }

        $information->addSocialNetwork($socialNetwork);
    }

    /**
     * Add phones
     *
     * @param Information $information
     * @param string $locale
     * @param string $defaultLocale
     */
    private function addPhones(Information $information, string $locale, string $defaultLocale)
    {
        $phones = !empty($this->yamlConfiguration['phones'][$locale])
            ? $this->yamlConfiguration['phones'][$locale] : (!empty($this->yamlConfiguration['phones'][$defaultLocale])
                ? $this->yamlConfiguration['phones'][$defaultLocale] : []);

        foreach ($phones as $phoneData) {

            $zones = isset($phoneData['zones']) && is_array($phoneData['zones']) ? $phoneData['zones'] : self::ZONES;

            $phone = new Phone();
            $phone->setLocale($locale);
            $phone->setNumber($phoneData['number']);
            $phone->setTagNumber($phoneData['tag_number']);
            $phone->setType($phoneData['type']);
            $phone->setZones($zones);

            $information->addPhone($phone);
        }
    }

    /**
     * Add emails
     *
     * @param Information $information
     * @param string $locale
     * @param string $defaultLocale
     */
    private function addEmails(Information $information, string $locale, string $defaultLocale)
    {
        $haveSupport = false;
        $haveNoReply = false;

        $emails = !empty($this->yamlConfiguration['emails'][$locale])
            ? $this->yamlConfiguration['emails'][$locale] : (!empty($this->yamlConfiguration['emails'][$defaultLocale])
                ? $this->yamlConfiguration['emails'][$defaultLocale] : []);

        foreach ($emails as $emailData) {

            $zones = isset($emailData['zones']) && is_array($emailData['zones']) ? $emailData['zones'] : self::ZONES;
            $slug = !empty($emailData['slug']) ? $emailData['slug'] : NULL;
            $deletable = true;

            if ($slug === 'support' || $slug === 'no-reply') {
                $deletable = false;
            }

            if ($slug === 'support') {
                $haveSupport = true;
            } elseif ($slug === 'no-reply') {
                $haveNoReply = true;
            }

            $email = new Email();
            $email->setLocale($locale);
            $email->setSlug($slug);
            $email->setDeletable($deletable);
            $email->setEmail($emailData['email']);

            if ($slug !== 'support' && $slug !== 'no-reply') {
                $email->setZones($zones);
            }

            $information->addEmail($email);
        }

        if (!$haveSupport) {
            $supportEmail = new Email();
            $supportEmail->setSlug('support');
            $supportEmail->setLocale($locale);
            $supportEmail->setEmail("support@felix-creation.fr");
            $supportEmail->setDeletable(false);
            $information->addEmail($supportEmail);
        }

        if (!$haveNoReply) {
            $noReplyEmail = new Email();
            $noReplyEmail->setLocale($locale);
            $noReplyEmail->setSlug('no-reply');
            $noReplyEmail->setEmail("no-reply@felix-creation.fr");
            $noReplyEmail->setDeletable(false);
            $information->addEmail($noReplyEmail);
        }
    }

    /**
     * Add addresses
     *
     * @param Information $information
     * @param string $locale
     * @param string $defaultLocale
     */
    private function addAddresses(Information $information, string $locale, string $defaultLocale)
    {
        $addresses = !empty($this->yamlConfiguration['addresses'][$locale])
            ? $this->yamlConfiguration['addresses'][$locale] : (!empty($this->yamlConfiguration['addresses'][$defaultLocale])
                ? $this->yamlConfiguration['addresses'][$defaultLocale] : []);

        foreach ($addresses as $addressData) {

            $zones = isset($addressData['zones']) && is_array($addressData['zones']) ? $addressData['zones'] : array_merge(self::ZONES, ['email']);

            $address = new Address();
            $address->setLocale($locale);
            $address->setName($addressData['name']);
            $address->setAddress($addressData['address']);
            $address->setZipCode($addressData['zip_code']);
            $address->setCity($addressData['city']);
            $address->setDepartment($addressData['department']);
            $address->setCountry($addressData['country']);
            $address->setZones($zones);

            $information->addAddress($address);
        }
    }

    /**
     * Add Legacy
     *
     * @param Information $information
     * @param string $locale
     * @param string $defaultLocale
     */
    private function addLegacy(Information $information, string $locale, string $defaultLocale)
    {
        $legalsData = !empty($this->yamlConfiguration['legals'][$locale])
            ? $this->yamlConfiguration['legals'][$locale] : (!empty($this->yamlConfiguration['legals'][$defaultLocale])
                ? $this->yamlConfiguration['legals'][$defaultLocale] : []);

        $legal = new Legal();
        $legal->setLocale($locale);

        foreach ($legalsData as $name => $value) {
            $setter = 'set' . ucfirst($name);
            $legal->$setter($value);
        }

        $information->addLegal($legal);
    }

    /**
     * Add i18n
     *
     * @param Information $information
     * @param string $locale
     */
    private function addI18n(Information $information, string $locale)
    {
        $companyName = !empty($this->yamlConfiguration['company_name']) ? $this->yamlConfiguration['company_name'] : NULL;

        $i18n = new i18n();
        $i18n->setLocale($locale);
        $i18n->setTitle($companyName);
        $i18n->setWebsite($information->getWebsite());

        $information->addI18n($i18n);
    }
}