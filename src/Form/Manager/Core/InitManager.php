<?php

namespace App\Form\Manager\Core;

use App\Controller\Front\InitController;
use Exception;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * InitManager
 *
 * Manage new project configuration
 *
 * @property array IPS_DEV
 *
 * @property KernelInterface $kernel
 * @property TranslatorInterface $translator
 * @property Request $request
 * @property string $configDirname
 * @property array $configuration
 * @property string $cmdBase
 * @property array $yaml
 * @property string $locale
 * @property array $locales
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class InitManager
{
    private const ACTIVATED = true;
    private const IPS_DEV = ['::1', '127.0.0.1', 'fe80::1'];

    private $kernel;
    private $translator;
    private $request;
    private $configDirname;
    private $configuration = [];
    private $cmdBase;
    private $yaml = [];
    private $locale;
    private $locales = [];

    /**
     * InitManager constructor.
     *
     * @param KernelInterface $kernel
     * @param TranslatorInterface $translator
     * @param RequestStack $requestStack
     * @throws Exception
     */
    public function __construct(KernelInterface $kernel, TranslatorInterface $translator, RequestStack $requestStack)
    {
        $this->request = $requestStack->getMasterRequest();
        $this->kernel = $kernel;
        $this->translator = $translator;

        $this->checkAccess();

        $configDirname = $this->kernel->getProjectDir() . '/bin/data/config/default.yaml';
        $this->configDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $configDirname);

        $phpBinaryFinder = new PhpExecutableFinder();
        $phpBinaryPath = $phpBinaryFinder->find();
        $cmdBase = $phpBinaryPath . ' ' . $this->kernel->getProjectDir() . '/bin/console ';
        $this->cmdBase = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $cmdBase);
    }


    /**
     * Check if user is not allowed to access this page
     *
     * @throws Exception
     */
    public function checkAccess()
    {
        $ipAllowed = !empty($_SERVER['REMOTE_ADDR']) ? in_array(@$_SERVER['REMOTE_ADDR'], self::IPS_DEV, true) : false;
        $denied = !self::ACTIVATED || !$ipAllowed || $this->kernel->getEnvironment() !== 'dev';

        if ($denied && is_object($this->request) && method_exists($this->request, 'getSchemeAndHttpHost')) {
            header('Location:' . $this->request->getSchemeAndHttpHost() . '/error.php');
            exit;
        } elseif ($denied) {
            throw new AccessDeniedException('Access denied', 403);
        }
    }

    /**
     * Execute
     *
     * @param FormInterface $form
     * @param array $steps
     * @param int $stepPosition
     * @param string $step
     * @return bool|string
     * @throws Exception
     */
    public function execute(FormInterface $form, array $steps, int $stepPosition, string $step)
    {
        $data = $form->getData();
        $this->getConfiguration();

        if ($step === 'data-base') {
            $isValid = $this->testConnection($data);
            if (!$isValid) {
                return false;
            }
        } else {
            $this->configuration = array_merge($this->configuration, $data);
            $this->setConfiguration();
        }

        if ($this->request->get('step') === 'styles') {
            $this->setSassVars();
        }

        return $this->getNextStep($stepPosition, $steps);
    }

    /**
     * Get next step
     *
     * @param int $stepPosition
     * @param array $steps
     * @return null|string
     */
    private function getNextStep(int $stepPosition, array $steps)
    {
        $nextStepPosition = $stepPosition + 1;
        if ($nextStepPosition <= count($steps)) {
            foreach ($steps as $step => $config) {
                if ($config['position'] === $stepPosition && !empty($config['nextStep'])) {
                    return $config['nextStep'];
                } elseif ($config['position'] === $nextStepPosition) {
                    return $step;
                }
            }
        }
    }

    /**
     * Get configuration
     */
    private function getConfiguration()
    {
        $filesystem = new Filesystem();
        if (!$filesystem->exists($this->configDirname)) {
            $filesystem->dumpFile($this->configDirname, '');
            $this->configuration = [];
        }
        $yamlConfiguration = Yaml::parseFile($this->configDirname);
        $this->configuration = $yamlConfiguration ? $yamlConfiguration : [];
    }

    /**
     * Set configuration
     */
    private function setConfiguration()
    {
        $yaml = Yaml::dump($this->configuration);
        file_put_contents($this->configDirname, $yaml);
    }

    /**
     * To generate Yaml configuration file
     */
    private function generateConfiguration()
    {
        $this->yaml = [];
        $this->locale = $this->configuration['locale'];
        $this->locales = $this->configuration['locales_others'];
        array_unshift($this->locales, $this->locale);
        $this->locales = array_unique($this->locales);

        foreach ($this->configuration as $keyName => $data) {
            if (preg_match('/_domain/', $keyName)) {
                $this->yaml['domains'][$this->locale][$data] = $keyName === 'locale_domain';
            } elseif (preg_match('/network_/', $keyName)) {
                foreach ($this->locales as $locale) {
                    $this->yaml['social_networks'][$locale][str_replace('network_', '', $keyName)] = $data;
                }
            } elseif (preg_match('/legal_/', $keyName)) {
                foreach ($this->locales as $locale) {
                    $this->yaml['legals'][$locale][str_replace('legal_', '', $keyName)] = $data;
                }
            } elseif (preg_match('/google_/', $keyName)) {
                foreach ($this->locales as $locale) {
                    $this->yaml['apis'][$locale]['google'][str_replace('google_', '', $keyName)] = $data;
                }
            } elseif (preg_match('/color_/', $keyName)) {
                $this->yaml['colors'][str_replace('color_', '', $keyName)] = $data;
            } else {
                $this->yaml[$keyName] = $data;
            }
        }

        $this->yaml['media_path_duplication'] = 'default';

        $this->setPhoneNumbers();
        $this->setEmails();
        $this->setAddresses();
        $this->setColors();

        $this->configuration = $this->yaml;
    }

    /**
     * Set phone numbers
     */
    private function setPhoneNumbers()
    {
        foreach ($this->locales as $locale) {
            $this->yaml['phones'][$locale][] = [
                'number' => $this->configuration['phone_number'],
                'tag_number' => $this->configuration['phone_tag_number'],
                'type' => 'office',
                'zones' => ['contact', 'footer', 'email']
            ];
        }
    }

    /**
     * Set emails
     */
    private function setEmails()
    {
        foreach ($this->locales as $locale) {
            $this->yaml['emails'][$locale][] = [
                'email' => $this->configuration['contact_email'],
                'zones' => ['contact', 'footer']
            ];
            $this->yaml['emails'][$locale][] = [
                'slug' => "support",
                'email' => $this->configuration['contact_email'],
                'zones' => []
            ];
            $this->yaml['emails'][$locale][] = [
                'slug' => "no-reply",
                'email' => $this->configuration['no_reply_email'],
                'zones' => []
            ];
        }
    }

    /**
     * Set addresses
     */
    private function setAddresses()
    {
        foreach ($this->locales as $locale) {
            $this->yaml['addresses'][$locale][] = [
                'name' => $this->configuration['company_name'],
                'address' => $this->configuration['address_address'],
                'zip_code' => $this->configuration['zip_code_address'],
                'city' => $this->configuration['city_address'],
                'department' => $this->configuration['department_address'],
                'country' => strtoupper($this->locale),
                'zones' => ['contact', 'footer', 'email', 'maintenance']
            ];
        }
    }

    /**
     * Set colors
     */
    private function setColors()
    {
        $colors = [
            'primary' => '#ef7427',
            'secondary' => '#add4d3',
            'success' => '#5d7808',
            'info' => '#0081ac',
            'warning' => '"#f0ad4e"',
            'danger' => '#a82835',
            'danger_light' => '#ee9da5',
            'light' => '#dee2e6',
        ];

        $this->yaml['active_colors'] = [
            'white',
            'alert-success', 'alert-danger', 'alert-warning', 'alert-info',
            'mask-icon', 'msapplication-TileColor', 'theme-color', 'webmanifest-theme', 'webmanifest-background', 'browserconfig'
        ];

        /** Default colors */
        $this->yaml['colors']['white'] = '#ffffff';
        foreach ($colors as $code => $defaultHexadecimal) {
            $hexadecimal = !empty($this->configuration['color_' . $code]) ? $this->configuration['color_' . $code] : $defaultHexadecimal;
            $this->yaml['colors'][str_replace('_', '-', $code)] = $hexadecimal;
            if (!empty($this->configuration['color_' . $code]) && !in_array($code, $this->yaml['active_colors'])) {
                $this->yaml['active_colors'][] = str_replace('_', '-', $code);
            }
        }

        /** Unset key with underscore */
        foreach ($this->yaml['colors'] as $code => $hexadecimal) {
            if (preg_match('/_/', $code)) {
                unset($this->yaml['colors'][$code]);
            }
        }

        $this->yaml['colors']['dark'] = '#343a40';
        $this->yaml['colors']['link'] = $this->yaml['colors']['primary'];

        /** Favicons colors */
        $this->yaml['favicons']['mask-icon'] = $this->yaml['colors']['primary'];
        $this->yaml['favicons']['msapplication-TileColor'] = $this->yaml['colors']['primary'];
        $this->yaml['favicons']['theme-color'] = $this->yaml['colors']['white'];
        $this->yaml['favicons']['webmanifest-theme'] = $this->yaml['colors']['white'];
        $this->yaml['favicons']['webmanifest-background'] = $this->yaml['colors']['white'];
        $this->yaml['favicons']['browserconfig'] = $this->yaml['colors']['primary'];
    }

    /**
     * To set mySQL connection
     *
     * @param array $data
     * @return string|bool
     * @throws Exception
     */
    private function testConnection(array $data)
    {
        $session = new Session();

        try {
            $dbh = new \PDO(
                'mysql:host=' . $data['host'] . ';dbname=' . $data['db_name'] . ';port=' . $data['port'], $data['user'], $data['password'],
                [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']);
            $tables = $dbh->query("SHOW TABLES");
            while ($row = $tables->fetch(\PDO::FETCH_NUM)) {
                $session->getFlashBag()->add('danger', $this->translator->trans("Cette table existe déjà.", [], 'core_init'));
                return false;
            }
        } catch (\PDOException $exception) {
            if ($exception->getCode() !== 1049) {
                $session = new Session();
                $session->getFlashBag()->add('danger', utf8_encode($exception->getMessage()));
                return false;
            }
        }

        return $this->setGlobalVars($data);
    }

    /**
     * To set sass vars
     */
    private function setSassVars()
    {
        $dirname = $this->kernel->getProjectDir() . '/assets/scss/front/default/variables.scss';
        $dirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $dirname);
        $content = file_get_contents($dirname);
        $content = $this->setSassColors($content);
        $content = $this->setSassFont($content);

        file_put_contents($dirname, $content);
    }

    /**
     * Set sass colors vars
     *
     * @param $content
     * @return string
     */
    private function setSassColors(string $content)
    {
        $colors = ['primary', 'secondary', 'success', 'info', 'warning', 'danger', 'danger_light', 'light'];

        foreach ($colors as $color) {
            if (!empty($this->configuration['color_' . $color])) {
                $var = str_replace('_', '-', $color);
                preg_match("/" . $var . ":(.*)!default;/", $content, $matches);
                if (!empty($matches[0])) {
                    $content = str_replace($matches[0], $var . ": " . $this->configuration['color_' . $color] . " !default;", $content);
                }
            }
        }

        return $content;
    }

    /**
     * Set sass fonts vars
     *
     * @param $content
     * @return string
     */
    private function setSassFont(string $content)
    {
        $fontName = !empty($this->configuration['fonts'][0]) ? $this->configuration['fonts'][0] : NULL;
        if ($fontName) {
            $filesystem = new Filesystem();
            $dirname = $this->kernel->getProjectDir() . '/assets/lib/fonts/' . $fontName . '.scss';
            $dirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $dirname);
            if ($filesystem->exists($dirname)) {
                preg_match("/fontFamily:(.*);/", file_get_contents($dirname), $fontFamily);
                if (!empty($fontFamily[1])) {
                    preg_match("/font-main:(.*);/", $content, $mainFont);
                    $content = !empty($mainFont[1]) ? str_replace(trim($mainFont[1]), trim($fontFamily[1]), $content) : $content;
                    preg_match("/font-light:(.*);/", $content, $mainFont);
                    $content = !empty($mainFont[1]) ? str_replace(trim($mainFont[1]), trim($fontFamily[1]), $content) : $content;
                    preg_match("/font-secondary:(.*);/", $content, $mainFont);
                    $content = !empty($mainFont[1]) ? str_replace(trim($mainFont[1]), trim($fontFamily[1]), $content) : $content;
                }
            }
        }

        return $content;
    }

    /**
     * To set .env vars
     *
     * @param array $data
     * @return bool
     */
    private function setGlobalVars(array $data)
    {
        $this->generateConfiguration();
        $this->setConfiguration();

        $distDirname = $this->kernel->getProjectDir() . '/.env.dist';
        $distDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $distDirname);
        $content = file_get_contents($distDirname);

        /** Set global */
        $content = str_replace('APP_LOCALE=fr', 'APP_LOCALE=' . $this->configuration['locale'], $content);

        /** Set tokens */
        $content = str_replace('APP_SECRET=secret-token', 'APP_SECRET=' . $this->configuration['token_security'], $content);
        $content = str_replace('SECURITY_TOKEN=token-security', 'SECURITY_TOKEN=' . $this->configuration['token_security_back'], $content);

        /** Set Local DB params */
        $content = str_replace('DATABASE_PREFIX=', 'DATABASE_PREFIX=' . $data['prefix'], $content);
        $content = str_replace('DB_HOST_LOCAL=', 'DB_HOST_LOCAL=' . $data['host'], $content);
        $content = str_replace('DB_USER_LOCAL=', 'DB_USER_LOCAL=' . $data['user'], $content);
        $content = str_replace('DB_PASSWORD_LOCAL=', 'DB_PASSWORD_LOCAL=' . $data['password'], $content);
        $content = str_replace('DB_NAME_LOCAL=', 'DB_NAME_LOCAL=' . $data['db_name'], $content);
        $content = str_replace('DB_PORT_LOCAL=', 'DB_PORT_LOCAL=' . $data['port'], $content);

        file_put_contents($this->kernel->getProjectDir() . '/.env', $content);

        return true;
    }

    /**
     * Generate database
     *
     * @throws Exception
     */
    public function generateDBCreate()
    {
        $this->setKeys();

        try {
            new \PDO(
                'mysql:host=' . $_ENV['DB_HOST_LOCAL'] . ';dbname=' . $_ENV['DB_NAME_LOCAL'] . ';port=' . $_ENV['DB_PORT_LOCAL'], $_ENV['DB_USER_LOCAL'], $_ENV['DB_PASSWORD_LOCAL'],
                [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']);
        } catch (\PDOException $exception) {
            $this->processExe($this->cmdBase . 'doctrine:database:create');
        }

        $this->processExe($this->cmdBase . 'doctrine:schema:update --force');
    }

    /**
     * Generate fixtures
     *
     * @throws Exception
     */
    public function generateDBFixtures()
    {
        $this->processExe($this->cmdBase . 'doctrine:fixtures:load --no-interaction');
    }

    /**
     * Generate translations
     *
     * @throws Exception
     */
    public function generateDBTranslations()
    {
        $this->processExe($this->cmdBase . 'app:cmd:translations default');
    }

    /**
     * Yarn install
     *
     * @throws Exception
     */
    public function yarnInstall()
    {
        $jsonDirname = $this->kernel->getProjectDir() . '/public/js/fos_js_routes.json ';
        $jsonDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $jsonDirname);

        $this->processExe($this->cmdBase . 'assets:install');
        $this->processExe($this->cmdBase . 'fos:js-routing:dump --format=json --target=' . $jsonDirname);

        $dirname = $this->kernel->getProjectDir() . '/node_modules';
        $dirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $dirname);
        $filesystem = new Filesystem();
        if (!$filesystem->exists($dirname)) {
            $this->processExe('yarn install');
        }
    }

    /**
     * Encore generation
     *
     * @throws Exception
     */
    public function encoreInstall()
    {
        $this->processExe('yarn encore production');
    }

    /**
     * Execute process
     *
     * @param string $cmd
     * @param bool $debug
     * @return Process
     */
    private function processExe(string $cmd, bool $debug = false)
    {
        if ($this->kernel->getEnvironment() === 'dev') {

            $process = Process::fromShellCommandline($cmd);
            $process->setTimeout(NULL);
            $process->setIdleTimeout(NULL);
            $process->start();
            if ($debug) {
                $process->wait(function ($type, $buffer) {
                    if (Process::ERR === $type) {
                        echo 'ERR > ' . $buffer;
                    } else {
                        echo 'OUT > ' . $buffer;
                    }
                });
            } else {
                $process->wait();
            }

            return $process;
        }
    }

    /**
     * To unset unused keys
     */
    private function setKeys()
    {
        $this->getConfiguration();

        $this->configuration['is_init'] = true;

        unset($this->configuration['locale_domain']);
        unset($this->configuration['pre_prod_domain']);
        unset($this->configuration['prod_domain']);
        unset($this->configuration['phone_number']);
        unset($this->configuration['phone_tag_number']);
        unset($this->configuration['contact_email']);
        unset($this->configuration['no_reply_email']);
        unset($this->configuration['address_address']);
        unset($this->configuration['zip_code_address']);
        unset($this->configuration['city_address']);
        unset($this->configuration['department_address']);
        unset($this->configuration['token_security']);
        unset($this->configuration['token_security_back']);
        unset($this->configuration['token_security_back']);
        unset($this->configuration['host']);
        unset($this->configuration['user']);
        unset($this->configuration['password']);
        unset($this->configuration['db_name']);
        unset($this->configuration['port']);
        unset($this->configuration['prefix']);

        $this->setConfiguration();
    }

    /**
     * To disable all init services
     */
    public function disableServices()
    {
        $dirs = [
            $this->kernel->getProjectDir() . str_replace('App\\', '\\src\\', InitController::class . '.php'),
            $this->kernel->getProjectDir() . str_replace('App\\', '\\src\\', InitManager::class . '.php'),
        ];

        foreach ($dirs as $dirname) {
            $dirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $dirname);
            $filesystem = new Filesystem();
            if ($filesystem->exists($dirname)) {
                $content = str_replace('private const ACTIVATED = false;', 'private const ACTIVATED = false;', file_get_contents($dirname));
                file_put_contents($dirname, $content);
            }
        }
    }
}