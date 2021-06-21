<?php

namespace App\Service\Translation;

use App\Repository\Translation\TranslationRepository;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * Loader
 *
 * To load translations
 *
 * @property TranslationRepository $translationRepository
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class Loader implements LoaderInterface
{
    private $translationRepository;

    /**
     * Loader constructor.
     *
     * @param TranslationRepository $translationRepository
     */
    public function __construct(TranslationRepository $translationRepository)
    {
        $this->translationRepository = $translationRepository;
    }

    public function load($resource, string $locale, string $domain = 'messages'): MessageCatalogue
    {
        $messages = $this->translationRepository->findByDomainAndLocale($domain, $locale);

        $translations = [];
        foreach ($messages as $message) {
            $translations[$message->getUnit()->getKeyname()] = $message->getContent();
        }

        return new MessageCatalogue($locale, [
            $domain => $translations,
        ]);
    }
}