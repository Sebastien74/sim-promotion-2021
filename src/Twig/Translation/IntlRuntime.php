<?php

namespace App\Twig\Translation;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use IntlDateFormatter;
use IntlTimeZone;
use Locale;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Languages;
use Symfony\Component\Intl\Currencies;
use Twig\Environment;
use Twig\Extension\CoreExtension;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * IntlRuntime
 *
 * @property array LOCALES_CODES
 * @property Request $request
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class IntlRuntime implements RuntimeExtensionInterface
{
    private const LOCALES_CODES = [
        'EN' => 'GB'
    ];

    private $request;

    /**
     * IntlRuntime constructor.
     *
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack->getMasterRequest();
    }

    /**
     * Get countries names by locale
     *
     * @param null|string $locale
     *
     * @return array
     */
    public function countryNames(string $locale = NULL)
    {
        if ($locale) {
            Locale::setDefault($locale);
        }

        return Countries::getNames();
    }

    /**
     * Get country name by locale
     *
     * @param null|string $countryCode
     * @param null|string $locale
     *
     * @return string
     */
    public function countryName(string $countryCode = NULL, $locale = NULL)
    {
        if (!$countryCode) {
            return NULL;
        }

        if ($locale) {
            Locale::setDefault($locale);
        }

        $countryCode = !empty(self::LOCALES_CODES[strtoupper($countryCode)]) ? self::LOCALES_CODES[strtoupper($countryCode)] : $countryCode;

        return Countries::getName($countryCode);
    }

    /**
     * Get languages name
     *
     * @param null|string $locale
     *
     * @return string[]
     */
    public function languagesName(string $locale = NULL)
    {
        if ($locale) {
            Locale::setDefault($locale);
        }
        return Languages::getNames();
    }

    /**
     * Get language name by locale
     *
     * @param null|string $countryCode
     * @param null|string $locale
     *
     * @return string
     */
    public function languageName(string $countryCode, $locale = NULL)
    {
        if ($locale) {
            Locale::setDefault($locale);
        }

        return Languages::getName($countryCode);
    }

    /**
     * Get currencies names by locale
     *
     * @param string|null $locale
     *
     * @return array
     */
    public function currencyNames(string $locale = NULL)
    {
        if ($locale) {
            Locale::setDefault($locale);
        }
        return Currencies::getNames();
    }

    /**
     * Get currency name by locale
     *
     * @param null|string $currencyCode
     * @param null|string $locale
     *
     * @return string
     */
    public function currencyName(string $currencyCode = NULL, $locale = NULL)
    {
        if (!$currencyCode) {
            return NULL;
        }

        if ($locale) {
            Locale::setDefault($locale);
        }

        return Currencies::getName($currencyCode);
    }

    /**
     * Get currency symbol by locale
     *
     * @param string $currencyCode
     * @param null|string $locale
     *
     * @return string
     */
    public function currencySymbol(string $currencyCode, $locale = NULL)
    {
        if (!$currencyCode) {
            return NULL;
        }

        if ($locale) {
            Locale::setDefault($locale);
        }

        return Currencies::getSymbol($currencyCode);
    }

    /**
     * Converts an input to a \DateTime instance to locale string.
     *
     * @param Environment $env
     * @param $date
     * @param string $dateFormat
     * @param string $timeFormat
     * @param null $locale
     * @param null $timezone
     * @param null $format
     * @param string $calendar
     * @return bool|string
     * @throws Exception
     */
    public function localizedDate(Environment $env, $date, $dateFormat = 'medium', $timeFormat = 'medium', $locale = null, $timezone = null, $format = null, $calendar = 'gregorian')
    {
        $date = $this->twigDateConverter($env, $date, $timezone);

        $formatValues = array(
            'none' => IntlDateFormatter::NONE,
            'short' => IntlDateFormatter::SHORT,
            'medium' => IntlDateFormatter::MEDIUM,
            'long' => IntlDateFormatter::LONG,
            'full' => IntlDateFormatter::FULL,
        );

        if (PHP_VERSION_ID < 50500 || !class_exists('IntlTimeZone')) {
            $formatter = IntlDateFormatter::create(
                $locale,
                $formatValues[$dateFormat],
                $formatValues[$timeFormat],
                $date->getTimezone()->getName(),
                'gregorian' === $calendar ? IntlDateFormatter::GREGORIAN : IntlDateFormatter::TRADITIONAL,
                $format
            );

            return $formatter->format($date->getTimestamp());
        }

        $formatter = IntlDateFormatter::create(
            $locale,
            $formatValues[$dateFormat],
            $formatValues[$timeFormat],
            IntlTimeZone::createTimeZone($date->getTimezone()->getName()),
            'gregorian' === $calendar ? IntlDateFormatter::GREGORIAN : IntlDateFormatter::TRADITIONAL,
            $format
        );

        return $formatter->format($date->getTimestamp());
    }

    /**
     * Converts an input to a \DateTime instance.
     *
     * @param Environment $env
     * @param DateTimeInterface|string|null $date A date or null to use the current time
     * @param DateTimeZone|string|false|null $timezone The target timezone, null to use the default, false to leave unchanged
     * @return DateTimeInterface
     * @throws Exception
     */
    function twigDateConverter(Environment $env, $date = null, $timezone = null)
    {
        /** determine the timezone */
        if (false !== $timezone) {
            if (null === $timezone) {
                $timezone = $env->getExtension(CoreExtension::class)->getTimezone();
            } elseif (!$timezone instanceof DateTimeZone) {
                $timezone = new DateTimeZone($timezone);
            }
        }

        /** immutable dates */
        if ($date instanceof DateTimeImmutable) {
            return false !== $timezone ? $date->setTimezone($timezone) : $date;
        }

        if ($date instanceof DateTimeInterface) {
            $date = clone $date;
            if (false !== $timezone) {
                $date->setTimezone($timezone);
            }

            return $date;
        }

        if (null === $date || 'now' === $date) {
            return new DateTime($date, false !== $timezone ? $timezone : $env->getExtension(CoreExtension::class)->getTimezone());
        }

        $asString = (string) $date;
        if (ctype_digit($asString) || (!empty($asString) && '-' === $asString[0] && ctype_digit(substr($asString, 1)))) {
            $date = new DateTime('@'.$date);
        } else {
            $date = new DateTime($date, $env->getExtension(CoreExtension::class)->getTimezone());
        }

        if (false !== $timezone) {
            $date->setTimezone($timezone);
        }

        return $date;
    }

    /**
     * Get format Date by locale
     *
     * @param null|string $locale
     *
     * @return object
     * @throws Exception
     */
    public function formatDate($locale = NULL)
    {
        $locale = !$locale ? $this->request->getLocale() : $locale;
        $formatter = new IntlDateFormatter($locale, IntlDateFormatter::SHORT, IntlDateFormatter::SHORT);
        $fullOrigin = $formatter->getPattern();
        $matches = explode(' ', $fullOrigin);

        $matchesFormat = explode('/', $matches[0]);
        if ($matchesFormat[0] === 'dd') {
            $formatter->setPattern('DD/MM/YYYY H:i:s');
        } else {
            $formatter->setPattern('YYYY/MM/DD g:i:s A');
        }

        $large = $formatter->getPattern();
        $matchesLarge = explode(' ', $large);
        $monthDay = !empty($matches[0]) ? rtrim(ltrim(str_replace('y', '', $matches[0]), '/'), '/') : NULL;
        $month = rtrim(ltrim(str_replace('d', '', $monthDay), '/'), '/');

        if ($matchesFormat[0] === 'dd') {
            $formatter->setPattern('d/mm/yyyy');
        } else {
            $formatter->setPattern('yyyy/mm/d');
        }
        $datepicker = $formatter->getPattern();

        return (object)[
            'dateTime' => $fullOrigin,
            'dateTimeLarge' => $large,
            'date' => !empty($matches[0]) ? $matches[0] : NULL,
            'dateLarge' => !empty($matchesLarge[0]) ? $matchesLarge[0] : NULL,
            'dayMonth' => strtolower($monthDay),
            'dayMonthLarge' => $monthDay,
            'month' => strtolower($month),
            'monthLarge' => $month,
            'time' => !empty($matches[0]) ? $matches[0] : NULL,
            'timeLarge' => !empty($matches[1]) ? $matches[1] : NULL,
            'datepicker' => $datepicker,
        ];
    }
}