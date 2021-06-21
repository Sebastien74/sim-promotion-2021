<?php

namespace App\Twig\Content;

use App\Entity\Module\Map\Map;
use App\Entity\Information\Information;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * InformationRuntime
 *
 * @property EntityManagerInterface $entityManager
 * @property array $information
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class InformationRuntime implements RuntimeExtensionInterface
{
    private $entityManager;
    private $information = [];

    /**
     * InformationRuntime constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

	/**
	 * Get website contact information
	 *
	 * @param Information $information
	 * @param string $locale
	 * @return array
	 */
    public function info(Information $information, string $locale): array
	{
		$information = $this->entityManager->getRepository(Information::class)->findArray($information->getId());

        $this->information = [];

        if ($information['id'] > 0) {
            $this->getI18ns($information, $locale);
            $this->getPhones($information, $locale);
            $this->getEmails($information, $locale);
            $this->getAddresses($information, $locale);
            $this->getMap($information, $locale);
        }

        ksort($this->information);

        return $this->information;
    }

	/**
	 * Get i18n
	 *
	 * @param array $information
	 * @param string $locale
	 */
    private function getI18ns(array $information, string $locale)
    {
        foreach ($information['i18ns'] as $i18n) {
            if ($i18n['locale'] === $locale) {
                $this->information['i18n'] = $i18n;
                break;
            }
        }
    }

    /**
     * Get phones
     *
	 * @param array $information
     * @param string $locale
     */
    private function getPhones(array $information, string $locale)
    {
        foreach ($information['phones'] as $phone) {
            $phoneLocale = $phone['locale'];
            if (strtolower($phoneLocale) === $locale || empty($phoneLocale)) {
                foreach ($phone['zones'] as $zone) {
                    $this->information['phones'][$zone][] = $phone;
                    $this->information['phones']['all'][] = $phone;
                }
            }
            if (!empty($phoneLocale)) {
                foreach ($phone['zones'] as $zone) {
                    $this->information['phones']['locales'][$phoneLocale][$zone][] = $phone;
                }
            }
        }

        if (!empty($this->information['phones'])) {
            ksort($this->information["phones"]);
        }
    }

    /**
     * Get emails
     *
	 * @param array $information
     * @param string $locale
     */
    private function getEmails(array $information, string $locale)
    {
        foreach ($information['emails'] as $email) {
            $emailLocale = $email['locale'];
            if (strtolower($emailLocale) === $locale || empty($emailLocale)) {
                foreach ($email['zones'] as $zone) {
                    $this->information['emails'][$zone][] = $email;
                    $this->information['emails']['all'][] = $email;
                }
            }
            if (!empty($emailLocale)) {
                foreach ($email['zones'] as $zone) {
                    $this->information['emails']['locales'][$emailLocale][$zone][] = $email;
                }
            }
        }

        if (!empty($this->information['emails'])) {
            ksort($this->information["emails"]);
        }
    }

    /**
     * Get addresses
     *
	 * @param array $information
     * @param string $locale
     */
    private function getAddresses(array $information, string $locale)
    {
        foreach ($information['addresses'] as $address) {
            $addressLocale = $address['locale'];
            if (strtolower($addressLocale) === $locale || empty($addressLocale)) {
                foreach ($address['zones'] as $zone) {
                    $this->information['addresses'][$zone][] = $address;
                    $this->information['addresses']['all'] = $address;
                }
            }
            if (!empty($addressLocale)) {
                foreach ($address['zones'] as $zone) {
                    $this->information['addresses']['locales'][$addressLocale][$zone][] = $address;
                }
            }
        }

        if (!empty($this->information['addresses'])) {
            ksort($this->information["addresses"]);
        }
    }

    /**
     * Get main map
     *
	 * @param array $information
     * @param string $locale
     */
    private function getMap(array $information, string $locale)
    {
        $map = $this->entityManager->getRepository(Map::class)->findDefault($information['website']['id']);

        $this->information['map']['entity'] = $map;

        if ($map) {

            $this->information['map']['latitude'] = $map['latitude'];
            $this->information['map']['longitude'] = $map['longitude'];
            $this->information['map']['points'] = [];

            foreach ($map['points'] as $point) {

                $pointI18n = NULL;
                foreach ($point['i18ns'] as $i18n) {
                    if ($i18n['locale'] === $locale) {
                        $pointI18n = $i18n;
                        break;
                    }
                }

                $this->information['map']['points'][] = [
                    'i18n' => $pointI18n,
                    'address' => $point['address'],
                ];
            }
        }
    }
}