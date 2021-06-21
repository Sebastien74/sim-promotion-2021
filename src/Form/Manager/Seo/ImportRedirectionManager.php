<?php

namespace App\Form\Manager\Seo;

use App\Entity\Core\Website;
use App\Entity\Seo\Redirection;
use App\Repository\Seo\RedirectionRepository;
use App\Service\Core\XlsxFileReader;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * ImportRedirectionManager
 *
 * Manage SEO xls Redirection import
 *
 * @property EntityManagerInterface $entityManager
 * @property XlsxFileReader $fileReader
 * @property KernelInterface $kernel
 * @property RedirectionRepository $repository
 * @property array $mapping
 * @property array $iterations
 * @property bool|string $message
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ImportRedirectionManager
{
    private $entityManager;
    private $fileReader;
    private $kernel;
    private $repository;
    private $mapping = [];
    private $iterations = [];
    private $message = false;

    /**
     * ImportRedirectionManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param XlsxFileReader $fileReader
     * @param KernelInterface $kernel
     */
    public function __construct(EntityManagerInterface $entityManager, XlsxFileReader $fileReader, KernelInterface $kernel)
    {
        $this->entityManager = $entityManager;
        $this->fileReader = $fileReader;
        $this->kernel = $kernel;
        $this->repository = $this->entityManager->getRepository(Redirection::class);;
    }

    /**
     * Execute import
     *
     * @param Form $form
     * @param Website $website
     * @return bool
     * @throws Exception
     */
    public function execute(Form $form, Website $website)
    {
        $tmpFile = $form->getData()['xls_file'];
        $response = $this->fileReader->read($tmpFile);

        if (property_exists($response, 'iterations')) {

            $this->mapping = $response->mapping;
            $this->iterations = $response->iterations;

            if (!$this->isValidFile()) {
                return false;
            }
            if (!$this->iterations) {
                return false;
            }

            $this->cleanIterations();
            $this->parse($website);
        }

        return true;
    }

    private function cleanIterations()
    {
        // Delete empty rows
        foreach ($this->iterations as $key => $row) {

            if (empty($row['locale']) && empty($row['old']) && empty($row['new'])) {
                unset($this->iterations[$key]);
            }
        }
    }

    /**
     * Parse data
     * @param Website $website
     */
    private function parse(Website $website)
    {
        foreach ($this->iterations as $key => $row) {

            $existing = $this->getRedirection($website, $row['locale'], $row['old'], $row['new']);

            if (!$existing && $row['new'] && $row['old'] && $row['locale'] && $row['old'] !== '/') {
                $this->addRedirection($website, $row['locale'], $row['old'], $row['new']);
            } else {
                $this->logger($row['old'], $key, $row['new'], $row['locale']);
            }
        }

        if ($this->message) {
            $session = new Session();
            $session->getFlashBag()->add('error', "Une ou plusieurs redirections n'ont pas été générées. Consulter le fichier redirections.log.");
        }
    }

    /**
     * Get Redirection
     *
     * @param Website $website
     * @param string $locale
     * @param string|NULL $old
     * @param string|NULL $new
     * @return Redirection|null
     */
    private function getRedirection(Website $website, string $locale, string $old = NULL, string $new = NULL)
    {
        return $this->repository->findOneBy([
            'website' => $website,
            'locale' => $locale,
            'old' => $old,
            'new' => $new
        ]);
    }

    /**
     * Add Redirection
     *
     * @param Website $website
     * @param string $locale
     * @param string|NULL $old
     * @param string|NULL $new
     */
    private function addRedirection(Website $website, string $locale, string $old = NULL, string $new = NULL)
    {
        $redirection = new Redirection();
        $redirection->setWebsite($website);
        $redirection->setOld($old);
        $redirection->setNew($new);
        $redirection->setLocale($locale);

        $this->entityManager->persist($redirection);
        $this->entityManager->flush();
    }

    /**
     * Cacheck if is valid file
     */
    private function isValidFile()
    {
        $columns = ['locale', 'old', 'new'];
        $count = 0;
        foreach ($this->mapping as $header) {
            foreach ($header as $name => $column) {
                if (in_array($column, $columns)) {
                    $count++;
                }
            }
        }

        if ($count != count($columns)) {
            $session = new Session();
            $session->getFlashBag()->add('error', "Les entêtes ont été retirées du fichier d'origine.");
            return false;
        }

        return true;
    }

    /**
     * Log errors
     *
     * @param string|NULL $old
     * @param string|NULL $key
     * @param string|NULL $new
     * @param string|NULL $locale
     */
    private function logger(string $old = NULL, string $key = NULL, string $new = NULL, string $locale = NULL)
    {
        $this->message = true;

        $logger = new Logger('redirection');
        $logger->pushHandler(new RotatingFileHandler($this->kernel->getLogDir() . '/redirection.log', 10, Logger::INFO));

        if ($old !== '/') {
            $logger->info('Invalid URI ' . $old);
        } elseif (!empty($exist)) {
            $logger->info('Redirection for old URI ' . $old . ' already exist');
        }

        if (empty($locale)) {
            $logger->info('Locale empty.');
        }

        $logger->alert('Redirection failed: XLSX row => ' . $key . ' locale => ' . $locale . ' old =>  ' . $old . ' - new => ' . $new);
    }
}