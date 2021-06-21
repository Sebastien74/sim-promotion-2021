<?php

namespace App\Twig\Core;

use App\Entity\Security\UserFront;
use App\Service\Core\CacheService;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use libphonenumber\PhoneNumberUtil;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * AppRuntime
 *
 * @property Request $currentRequest
 * @property Request $masterRequest
 * @property CacheService $cacheService
 * @property RouterInterface $router
 * @property EntityManagerInterface $entityManager
 * @property KernelInterface $kernel
 * @property bool $isDebug
 * @property Filesystem $fileSystem
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class AppRuntime implements RuntimeExtensionInterface
{
    private $currentRequest;
    private $masterRequest;
    private $cacheService;
    private $router;
    private $entityManager;
    private $kernel;
    private $isDebug;
    private $fileSystem;

    /**
     * AppRuntime constructor.
     *
     * @param RequestStack $requestStack
     * @param CacheService $cacheService
     * @param RouterInterface $router
     * @param EntityManagerInterface $entityManager
     * @param KernelInterface $kernel
     * @param bool $isDebug
     */
    public function __construct(
        RequestStack $requestStack,
        CacheService $cacheService,
        RouterInterface $router,
        EntityManagerInterface $entityManager,
        KernelInterface $kernel,
        bool $isDebug)
    {
        $this->currentRequest = $requestStack->getCurrentRequest();
        $this->masterRequest = $requestStack->getMasterRequest();
        $this->cacheService = $cacheService;
        $this->router = $router;
        $this->entityManager = $entityManager;
        $this->kernel = $kernel;
        $this->isDebug = $isDebug;
        $this->fileSystem = new Filesystem();
    }

    /**
     * Find entity
     *
     * @param string $classname
     * @param mixed $id
     * @return void|object
     */
    public function find(string $classname, $id)
    {
        if (!is_numeric($id)) {
            return;
        }

        return $this->entityManager->getRepository($classname)->find($id);
    }

    /**
     * Check if is debug mode
     *
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->isDebug;
    }

    /**
     * Cache
     *
     * @param string $html
     * @return string|null
     * @throws InvalidArgumentException
     */
    public function processCached(string $html): ?string
    {
        return $this->cacheService->parse($html);
    }

    /**
     * Check if route exist in PHP CLASS
     *
     * @param string $routeName
     * @return boolean
     */
    public function routeExist(string $routeName): bool
    {
        return (null === $this->router->getRouteCollection()->get($routeName)) ? false : true;
    }

    /**
     * Get Class name
     *
     * @param $class
     * @return string
     */
    public function getClass($class): string
    {
        return get_class($class);
    }

    /**
     * Check if is Instance of
     *
     * @param mixed $var
     * @param string $instance
     * @return boolean
     */
    public function instanceof($var, string $instance): bool
    {
        return $var instanceof $instance;
    }

    /**
     * Check if is an object
     *
     * @param * $var
     * @return boolean
     */
    public function isObject($var): bool
    {
        return is_object($var);
    }

    /**
     * Check if is an array
     *
     * @param * $var
     * @return boolean
     */
    public function isArray($var): bool
    {
        return is_array($var);
    }

    /**
     * Check if is boolean
     *
     * @param * $var
     * @return boolean
     */
    public function isBool($var): bool
    {
        return is_bool($var);
    }

    /**
     * Check if is numeric value
     *
     * @param * $var
     * @return boolean
     */
    public function isNumeric($var): bool
    {
        return is_numeric($var);
    }

    /**
     * Check if is an email
     *
     * @param * $var
     * @return boolean
     */
    public function isEmail($var): bool
    {
        return filter_var($var, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Check if is phone
     *
     * @param * $var
     * @return boolean
     */
    public function isPhone($var): bool
    {
        foreach (Countries::getNames() as $code => $name) {
            $phoneUtil = PhoneNumberUtil::getInstance();
            try {
                if ($phoneUtil->parse($var, strtoupper($code))) {
                    return true;
                }
            } catch (Exception $exception) {
                return false;
            }
        }

        return false;
    }

    /**
     * Check if is DateTime
     *
     * @param string|object|null $var
     * @return boolean
     */
    public function isDateTime($var = NULL): bool
    {
        return $var instanceof DateTime;
    }

    /**
     * Check if string is DateTime
     *
     * @param string|object|null $var
     * @return array|null
     */
    public function stringToDate($var = NULL): ?array
    {
        $formats = ['Y-m-d H:i:s', 'Y-m-d'];

        foreach ($formats as $format) {
            if (DateTime::createFromFormat($format, $var) !== false) {
                return [
                    'datetime' => DateTime::createFromFormat($format, $var),
                    'format' => $format
                ];
            }
        }

        return NULL;
    }

    /**
     * Check if is UploadedFile
     *
     * @param string $var
     * @return boolean
     */
    public function isUploadedFile($var): bool
    {
        return $var instanceof UploadedFile;
    }

    /**
     * Check if is integer
     *
     * @param string $var
     * @return boolean
     */
    public function isInt($var): bool
    {
        return is_int($var);
    }

    /**
     * To add day(s) to date
     *
     * @param string|DateTime $date
     * @param int $nbr
     * @return DateTime
     * @throws Exception
     */
    public function addDay($date, int $nbr = 1): DateTime
    {
        $date = $date instanceof DateTime ? $date : new DateTime($date);
        return $date->add(new DateInterval('P' . $nbr . 'D'));
    }

    /**
     * To add minute(s) to date
     *
     * @param string|DateTime $date
     * @param int $nbr
     * @return DateTime
     * @throws Exception
     */
    public function addMinute($date, int $nbr = 1): DateTime
    {
        $date = $date instanceof DateTime ? $date : new DateTime($date);
        return $date->add(new DateInterval('PT' . $nbr . 'M'));
    }

    /**
     * Convert Object to Array
     *
     * @param mixed|null $object
     * @return array
     */
    public function objectToArray($object = NULL): array
    {
        $result = [];

        if ($this->isObject($object)) {
            $reflectionClass = $this->entityManager->getClassMetadata(get_class($object));
            $cols = array_merge($reflectionClass->fieldMappings, $reflectionClass->associationMappings);
            foreach ($cols as $property => $col) {
                $getter = 'get' . ucfirst($property);
                if (method_exists($object, $getter)) {
                    $result[$property] = $object->$getter();
                    if (!empty($col['targetEntity'])) {
                        $subObject = new $col['targetEntity']();
                        $reflectionSubClass = $this->entityManager->getClassMetadata(get_class($subObject));
                        $subCols = array_merge($reflectionSubClass->fieldMappings, $reflectionSubClass->associationMappings);
                        foreach ($subCols as $subProperty => $subCol) {
                            $subGetter = 'get' . ucfirst($subProperty);
                            if (method_exists($subObject, $subGetter)) {
                                $result[$property . '.' . $subProperty] = $subObject->$subGetter();
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Get entity value
     *
     * @param null $object
     * @param string|null $attribute
     * @return object|string|null
     */
    public function entityValue($object = NULL, string $attribute = NULL)
    {
        $result = NULL;

        if (is_object($object) && $attribute) {

            $reflectionClass = $this->entityManager->getClassMetadata(get_class($object));
            $cols = array_merge($reflectionClass->fieldMappings, $reflectionClass->associationMappings);
            $properties = explode('.', $attribute);

            foreach ($properties as $property) {
                $getter = 'get' . ucfirst($property);
                if (is_object($result) && method_exists($result, $getter)) {
                    $result = $result->$getter();
                } elseif (method_exists($object, $getter)) {
                    $result = $object->$getter();
                    if (!$object->getId() && !empty($cols[$property]) && !empty($cols[$property]['targetEntity'])) {
                        $result = new $cols[$property]['targetEntity']();
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Get an unique ID
     *
     * @return string
     */
    public function uniqId(): string
    {
        return uniqid();
    }

    /**
     * Get an unique chars ID
     *
     * @param int $length
     * @return string
     */
    public function charsId($length = 10): string
    {
        $charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $result = '';
        $count = strlen($charset);

        for ($i = 0; $i < $length; $i++) {
            $result .= $charset[mt_rand(0, $count - 1)];
        }

        return $result;
    }

    /**
     * Unset array key
     *
     * @param array $array
     * @param string $key
     * @return array
     */
    public function unset(array $array, string $key): array
    {
        unset($array[$key]);

        return $array;
    }

    /**
     * Html entity decode
     *
     * @param string|null $string $string
     *
     * @return string
     */
    public function unescape(string $string = NULL): string
    {
        $string = str_replace(["\n", "\r"], '', $string);
        return nl2br(html_entity_decode($string));
    }

    /**
     * Html entity decode
     *
     * @param string $name
     *
     * @return null
     */
    public function removeSession($name = NULL)
    {
        if ($name) {
            $session = new Session();
            $session->remove($name);
        }

        return NULL;
    }

    /**
     * Decode URL
     *
     * @param string|null $string $string
     * @return null|string
     */
    public function urlDecode(string $string = NULL): ?string
    {
        if ($string) {
            return urldecode($string);
        }

        return NULL;
    }

    /**
     * Serialize array
     *
     * @param array $array
     * @return null|string
     */
    public function serialize(array $array = []): ?string
    {
        if (is_array($array)) {
            return serialize($array);
        }

        return NULL;
    }

    /**
     * Unserialize array
     *
     * @param string|null $serialize
     * @return string|null
     */
    public function unserialize(string $serialize = NULL): ?string
    {
        if (is_string($serialize)) {
            return unserialize($serialize);
        }

        return NULL;
    }

    /**
     * Implode array
     *
     * @param array $pieces
     * @param string $glue
     * @return string
     */
    public function implode(array $pieces, string $glue): string
    {
        return implode($glue, $pieces);
    }

    /**
     * Get current request
     *
     * @return Request|null
     */
    public function currentRequest(): ?Request
    {
        return $this->currentRequest;
    }

    /**
     * Get current request
     *
     * @return Request|null
     */
    public function masterRequest(): ?Request
    {
        return $this->masterRequest;
    }

    /**
     * Get master request attribute value
     *
     * @param string $attribute
     * @return mixed
     */
    public function masterRequestGet(string $attribute)
    {
        return $this->masterRequest->get($attribute);
    }

    /**
     * Calculate percent
     *
     * @param int $finished
     * @param int $count
     * @return float|int
     */
    public function percent(int $finished, int $count)
    {
        return ($finished * 100) / $count;
    }

    /**
     * Get current Request Client IP
     *
     * @return string|null
     */
    public function currentIP(): ?string
    {
        return $this->currentRequest->getClientIp();
    }

    /**
     * Set entities tree
     *
     * @param array $entities
     * @return array
     */
    public function entityTree(array $entities): array
    {
        $tree = [];
        foreach ($entities as $entity) {
            $parent = $entity->getParent() ? $entity->getParent()->getId() : 'main';
            $tree[$parent][$entity->getPosition()] = $entity;
            ksort($tree[$parent]);
        }
        return $tree;
    }

    /**
     * Convert string date to Datetime
     *
     * @param null|string $stringDate
     * @return null|DateTime
     */
    public function datetime(string $stringDate = NULL): ?DateTime
    {
        if ($stringDate) {
            try {
                return new DateTime($stringDate);
            } catch (Exception $exception) {
                return NULL;
            }
        }

        return NULL;
    }

    /**
     * Count striptags chars
     *
     * @param null $string
     * @return int
     */
    public function countChars($string = NULL): int
    {
        return strlen(strip_tags($string));
    }

    /**
     * Count entity collection by property
     *
     * @param mixed|null $entity
     * @param string|null $property
     * @return int|null
     */
    public function countCollection($entity = NULL, string $property = NULL): ?int
    {
        $getter = 'get' . ucfirst($property);

        if (is_object($entity) && method_exists($entity, $getter) && is_iterable($entity->$getter())) {
            return count($entity->$getter());
        }

        return NULL;
    }

    /**
     * Check if in Admin
     *
     * @return boolean
     */
    public function inAdmin(): bool
    {
        return preg_match('/\/admin-' . $_ENV['SECURITY_TOKEN'] . '/', $this->masterRequest->getUri());
    }

    /**
     * Check if is UserFront instance
     *
     * @param null|UserFront $user
     * @return boolean
     */
    public function isUserFront($user = NULL): bool
    {
        return $user instanceof UserFront;
    }

    /**
     * File get content in project dir
     *
     * @param string|null $dirname
     * @return string|null
     */
    public function fileGetContent(string $dirname = NULL): ?string
    {
        if ($dirname) {
            return file_get_contents($this->kernel->getProjectDir() . DIRECTORY_SEPARATOR . 'templates/' . DIRECTORY_SEPARATOR . $dirname);
        }

        return NULL;
    }

    /**
     * File get content by URL
     *
     * @param string|null $url
     * @return string|null
     */
    public function fileGetContentURL(string $url = NULL): ?string
    {
        if ($url) {
            return file_get_contents($url);
        }

        return NULL;
    }

    /**
     * Get first key of array
     *
     * @param array $array
     * @return string|null
     */
    public function arrayKeyFirst(array $array = []): ?string
    {
        return array_key_first($array);
    }

    /**
     * Remove text between
     *
     * @param string $string
     * @param array $tags
     * @return string|null
     */
    public function removeBetween(string $string, array $tags): ?string
    {
        return preg_replace("/\\" . $tags[0] . "([^()]*+|(?R))*\\" . $tags[1] . "/", "", $string);
    }

    /**
     * Get environment variable
     *
     * @param string|null $name
     * @return bool
     */
    public function getEnv(string $name = NULL): bool
    {
        return $name && !empty($_ENV[$name]) ? $_ENV[$name] : false;
    }

    /**
     * Die in twig file
     *
     * @param string|object|null $message
     */
    public function killRender($message = null)
    {
        if ($this->isDebug) {
            dump($message);
            die;
        }
    }
}