<?php

namespace App\Command;

use App\Entity\Core\Configuration;
use App\Entity\Core\Website;
use App\Service\Development\CommandHelper;
use App\Service\Translation\Extractor;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * TranslationCommand
 *
 * Helper to run CMS commands
 *
 * @property string $defaultName
 * @property Extractor $extractor
 * @property CommandHelper $commandHelper
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TranslationCommand extends Command
{
    protected static $defaultName = 'app:cmd:translations';

    private $extractor;
    private $commandHelper;

    /**
     * TranslationCommand constructor.
     *
     * @param Extractor $extractor
     * @param CommandHelper $commandHelper
     */
    public function __construct(Extractor $extractor, CommandHelper $commandHelper)
    {
        $this->extractor = $extractor;
        $this->commandHelper = $commandHelper;

        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setDescription('To regenerate entities configuration.')
            ->addArgument('website', InputArgument::OPTIONAL, 'Website entity.')
            ->addArgument('symfony_style', InputArgument::OPTIONAL, 'SymfonyStyle entity.')
            ->addArgument('output', InputArgument::OPTIONAL, 'Command output.');
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = $this->commandHelper->getIo($input, $output);

        if (!$this->commandHelper->isAllowed($io)) {
            return 0;
        }

        $output = $this->commandHelper->getOutput($input, $output);
        $website = $this->commandHelper->getWebsite($input, $io);

        if ($website instanceof Website) {
            $configuration = $website->getConfiguration();
            $this->extract($website, $configuration, $io, $output);
            $this->generate($configuration, $io, $output);
            $this->initFiles($configuration, $io, $output);
        }

        return 0;
    }

    /**
     * Extraction
     *
     * @param Website $website
     * @param Configuration $configuration
     * @param SymfonyStyle $io
     * @param OutputInterface $output
     * @throws Exception
     */
    private function extract(Website $website, Configuration $configuration, SymfonyStyle $io, OutputInterface $output)
    {
        $defaultLocale = $configuration->getLocale();
        $locales = $configuration->getAllLocales();

        $output->writeln('<comment>Entities translations extraction progressing...</comment>');
        $this->extractor->extractEntities($website, $defaultLocale, $locales);
        $output->writeln('<info>Entities translations successfully extracted.</info>');
        $io->newLine();

        foreach ($locales as $locale) {
            $output->writeln('<comment>Project translations [' . strtoupper($locale) . '] extraction progressing...</comment>');
            $this->extractor->extract($locale);
            $output->writeln('<info>Project translations [' . strtoupper($locale) . '] successfully extracted.</info>');
            $io->newLine();
        }
    }

    /**
     * Generate translations
     *
     * @param Configuration $configuration
     * @param SymfonyStyle $io
     * @param OutputInterface $output
     */
    private function generate(Configuration $configuration, SymfonyStyle $io, OutputInterface $output)
    {
        $defaultLocale = $configuration->getLocale();
        $locales = $configuration->getAllLocales();

        $output->writeln('<comment>Yaml translations extraction progressing...</comment>');
        $yamlTranslations = $this->extractor->findYaml($locales);
        $output->writeln('<info>Yaml translations successfully extracted.</info>');
        $io->newLine();

        $count = 0;
        foreach ($yamlTranslations as $domain => $localeTranslations) {
            foreach ($localeTranslations as $locale => $translations) {
                $count = $count + count($translations);
            }
        }

        $output->writeln('<comment>Translations generation progressing...</comment>');
        $io->newLine();

        $io->progressStart($count);
        foreach ($yamlTranslations as $domain => $localeTranslations) {
            foreach ($localeTranslations as $locale => $translations) {
                foreach ($translations as $keyName => $content) {
                    $this->extractor->generateTranslation($defaultLocale, $locale, $domain, $content, $keyName);
                    $io->progressAdvance();
                }
            }
        }
        $io->progressFinish();
    }

    /**
     * Generate new yaml
     *
     * @param Configuration $configuration
     * @param SymfonyStyle $io
     * @param OutputInterface $output
     */
    private function initFiles(Configuration $configuration, SymfonyStyle $io, OutputInterface $output)
    {
        $output->writeln('<comment>Files regeneration progressing...</comment>');
        $this->extractor->initFiles($configuration->getAllLocales());
        $output->writeln('<info>Files successfully regenerated.</info>');
        $io->newLine();

        $output->writeln('<comment>Clearing cache...</comment>');
        $this->extractor->clearCache();

        $io->newLine();
        $io->block('Translations successfully generated.', 'OK', 'fg=black;bg=green', ' ', true);
    }
}