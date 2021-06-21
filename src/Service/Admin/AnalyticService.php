<?php

namespace App\Service\Admin;

use App\Entity\Core\Website;
use App\Entity\Seo\Session;
use App\Twig\Content\BrowserRuntime;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * AnalyticService
 *
 * To manage admin analytics data
 *
 * @property EntityManagerInterface $entityManager
 * @property KernelInterface $kernel
 * @property BrowserRuntime $browserRuntime
 * @property array $allSession
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class AnalyticService
{
    private $entityManager;
    private $kernel;
    private $browserRuntime;
    private $allSession = [];

    /**
     * AnalyticsService constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param KernelInterface $kernel
     * @param BrowserRuntime $browserRuntime
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        KernelInterface $kernel,
        BrowserRuntime $browserRuntime)
    {
        $this->entityManager = $entityManager;
        $this->kernel = $kernel;
        $this->browserRuntime = $browserRuntime;
    }

    /**
     * Get file cache for admin view
     *
     * @param Website $website
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @return array
     * @throws Exception
     */
    public function getCache(Website $website, DateTime $startDate, DateTime $endDate)
    {
        $filesystem = new Filesystem();
        $dirname = $this->getCacheDirname($website, $startDate, $endDate);

//        if($filesystem->exists($dirname)) {
//
//            $file = new File($dirname);
//            $currentDate = new DateTime('now');
//            $fileDate = new DateTime();
//            $fileDate->setTimestamp($file->getMTime());
//            $diff = $fileDate->diff($currentDate);
//
//            if($diff->i < 5) {
//                return Yaml::parseFile($dirname);
//            }
//        }

        return $this->generateCache($website, $startDate, $endDate);
    }

    /**
     * Generate cache for admin view
     *
     * @param Website $website
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @return array
     */
    public function generateCache(Website $website, DateTime $startDate, DateTime $endDate)
    {
        $cache = [];
        $repository = $this->entityManager->getRepository(Session::class);
        $others = $repository->findWebsiteData($website, $startDate);
        $users = $repository->findWebsiteData($website, $startDate, $endDate);

        $cache['others'] = $this->parseSessions($others);
        $cache['users'] = $this->parseSessions($users);
        $cache['extraData'] = $this->extraData($cache['others'], $cache['users']);

        $sessions = $repository->findWebsiteData($website, $startDate, $endDate, $cache['extraData']['sessions']);
        $cache['sessions'] = $this->parseSessions($sessions);

        $dirname = $this->getCacheDirname($website, $startDate, $endDate);
        $cache = Yaml::dump($cache);
        file_put_contents($dirname, $cache);

        return Yaml::parseFile($dirname);
    }

    /**
     * To parse Sessions
     *
     * @param $sessions
     * @return array
     */
    private function parseSessions($sessions)
    {
        $cache = [];

        foreach ($sessions as $category => $values) {

            foreach ($values as $keyName => $value) {

                if ($value instanceof Session) {
                    $cache[$category][] = $this->getSessionForCache($value);
                    $cache['browsers'][$this->getBrowser($value)][] = $this->getSessionForCache($value);
                    $cache['anonymize'][] = $value->getGroup()->getAnonymize();
                    ksort($cache['browsers']);
                    $this->allSession[$value->getGroup()->getAnonymize()] = $value;
                } elseif ($category === 'pages') {
                    $cache[$category][$keyName] = $this->getPageForCache($value);
                } else {

                    foreach ($value as $key => $subValue) {
                        if ($category == 'countries' && $key === 'latitude') {
                            $cache[$category][$keyName][$key] = $subValue;
                        } elseif ($category == 'countries' && $key === 'longitude') {
                            $cache[$category][$keyName][$key] = $subValue;
                        } elseif ($category == 'countries' && $key === 'iso') {
                            $cache[$category][$keyName][$key] = $subValue;
                        } elseif ($subValue instanceof Session) {
                            $cache[$category][$keyName][] = $this->getSessionForCache($subValue);
                        } elseif (is_array($subValue)) {
                            foreach ($subValue as $subKey => $session) {
                                if ($session instanceof Session) {
                                    $cache[$category][$keyName][$key][] = $this->getSessionForCache($session);
                                }
                            }
                        }
                    }
                }
            }
        }

        return $cache;
    }

    /**
     * Get extra data
     *
     * @param array $others
     * @param array $sessions
     * @return array
     */
    private function extraData(array $others, array $sessions)
    {
        $pageCount = 0;
        $reboundCount = 0;
        $data = [];
        $sessionsSet = [];

        /** Get new session */
        $data['sessions'] = [];
        $data['newSessions'] = $count = 0;
        $data['pagesSessions'] = 0;
        $data['reboundCount'] = 0;
        $data['reboundDays'] = [];
        $data['reboundDayHours'] = [];

        if ($sessions) {

            foreach ($sessions['anonymize'] as $anonymize) {
                $alreadyPush = in_array($anonymize, $sessionsSet);
                if (!$alreadyPush && empty($others['anonymize']) || !$alreadyPush && !in_array($anonymize, $others['anonymize'])) {
                    $count++;
                    $data['newSessions'] = $count;
                    $sessionsSet[] = $anonymize;
                }
            }

            foreach ($sessions['all'] as $session) {
                if (in_array($session['anonymize'], $sessionsSet)) {
                    $pageCount = $pageCount + count($session['urls']);
                    $data['reboundDays'][$session['day']]['all'][] = $session;
                    $data['reboundDays'][$session['day']]['hours'][$session['hour']]['all'][] = $session;
                    if (count($session['urls']) === 1) {
                        $data['reboundDays'][$session['day']]['count'][] = $session;
                        $data['reboundDays'][$session['day']]['hours'][$session['hour']]['count'][] = $session;
                        $data['sessions'][$session['anonymize']] = $this->allSession[$session['anonymize']];
                        $reboundCount++;
                    }
                }
            }

            /** Get Pages/sessions */
            $data['pagesSessions'] = $pageCount / count($sessions['all']);
            $data['reboundCount'] = ($reboundCount / count($sessions['all'])) * 100;

            foreach ($data['reboundDays'] as $day => $pageSessions) {

                if (empty($pageSessions['count'])) {
                    $data['reboundDays'][$day]['rebound'] = 0;
                } else {
                    $data['reboundDays'][$day]['rebound'] = (count($pageSessions['count']) / count($pageSessions['all'])) * 100;
                }

                foreach ($pageSessions['hours'] as $hour => $hourSessions) {
                    if (empty($hourSessions['count'])) {
                        $data['reboundDayHours'][$hour]['rebound'] = 0;
                    } else {
                        $data['reboundDayHours'][$hour]['rebound'] = (count($hourSessions['count']) / count($hourSessions['all'])) * 100;
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Get Browser for cache
     *
     * @param Session $session
     * @return string
     */
    private function getBrowser(Session $session)
    {
        $browsers = ['Firefox', 'Chrome', 'Safari', 'Edge', 'IE', 'Opera'];

        if ($session->getUserAgent()) {
            foreach ($browsers as $browser) {
                $checker = 'is' . $browser;
                if ($this->browserRuntime->$checker($session->getUserAgent())) {
                    return strtolower($browser);
                }
            }
        }

        return 'undefined';
    }

    /**
     * Get Session for cache
     *
     * @param Session $session
     * @return array
     */
    private function getSessionForCache(Session $session)
    {
        $cache['id'] = $session->getId();
        $cache['createdAt'] = $session->getCreatedAt()->format('Y-m-d');
        $cache['day'] = $session->getDay();
        $cache['hour'] = $session->getCreatedAt()->format('H:i');
        $cache['screen'] = $session->getScreen();
        $cache['lastActivity'] = $session->getLastActivity()->format('Y-m-d');
        $cache['group'] = $session->getGroup()->getId();
        $cache['website'] = $session->getWebsite()->getId();
        $cache['userAgent'] = $session->getUserAgent();
        $cache['anonymize'] = $session->getGroup()->getAnonymize();

        foreach ($session->getUrls() as $url) {
            $cache['urls'][] = [
                'id' => $url->getId(),
                'createdAt' => $url->getCreatedAt()->format('Y-m-d'),
                'refererUri' => $url->getRefererUri(),
                'uri' => $url->getUri(),
                'url' => $url->getUrl() ? $url->getUrl()->getId() : NULL,
                'session' => $url->getSession()->getId(),
            ];
        }

        return $cache;
    }

    /**
     * Get Session for cache
     *
     * @param array $values
     * @return array|null
     */
    private function getPageForCache(array $values)
    {
        if (!empty($values['count'])) {

            $cache['count'] = $values['count'];

            foreach ($values['sessions'] as $session) {
                $cache['sessions'][] = $this->getSessionForCache($session);
            }

            return $cache;
        } else {
            return $values;
        }
    }

    /**
     * Get file cache dirname
     *
     * @param Website $website
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @return string
     */
    private function getCacheDirname(Website $website, DateTime $startDate, DateTime $endDate)
    {
        $cacheDirname = $this->kernel->getCacheDir() . '/doctrine/analytics/';
        $cacheDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $cacheDirname);

        $filesystem = new Filesystem();
        if (!$filesystem->exists($cacheDirname)) {
            $filesystem->mkdir($cacheDirname, 0777);
        }

        $fileName = 'analytics-' . $website->getId() . '-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d') . '.yaml';
        return $cacheDirname . $fileName;
    }
}