<?php

namespace App\Repository\Seo;

use App\Entity\Core\Website;
use App\Entity\Seo\Session;
use App\Entity\Seo\SessionGroup;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\HttpFoundation\Request;

/**
 * SessionRepository
 *
 * @method Session|null find($id, $lockMode = null, $lockVersion = null)
 * @method Session|null findOneBy(array $criteria, array $orderBy = null)
 * @method Session[]    findAll()
 * @method Session[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SessionRepository extends ServiceEntityRepository
{
    /**
     * SessionRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Session::class);
    }

    /**
     * Get Session by Day
     *
     * @param Request $request
     * @param SessionGroup $group
     * @param Website $website
     * @param string $tokenSession
     * @return Session|null
     * @throws NonUniqueResultException
     */
    public function findOneByDayAndGroup(Request $request, SessionGroup $group, Website $website, string $tokenSession)
    {
        $sessionDate = $request->getSession()->get('ANALYTICS_CREATED_AT_DEV');
        $date = $sessionDate ? $sessionDate : new DateTime('now');

        return $this->createQueryBuilder('s')
            ->andWhere('s.day = :day')
            ->andWhere('s.website = :website')
            ->andWhere('s.tokenSession = :tokenSession')
            ->andWhere('s.group = :group')
            ->setParameter('day', $date->format('Y-m-d'))
            ->setParameter('website', $website)
            ->setParameter('tokenSession', $tokenSession)
            ->setParameter('group', $group)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get Session by Day
     *
     * @param string $anonymize
     * @param Website $website
     * @param string $tokenSession
     * @return Session|null
     */
    public function findByAnonymizeAndSession(string $anonymize, Website $website, string $tokenSession)
    {
        $sessions = $this->createQueryBuilder('s')
            ->leftJoin('s.group', 'g')
            ->andWhere('s.website = :website')
            ->andWhere('s.tokenSession = :tokenSession')
            ->andWhere('g.anonymize = :anonymize')
            ->setParameter('website', $website)
            ->setParameter('tokenSession', $tokenSession)
            ->setParameter('anonymize', $anonymize)
            ->addSelect('g')
            ->getQuery()
            ->getResult();

        return $sessions ? $sessions[0] : NULL;
    }

    /**
     * Get Session[] by Website
     *
     * @param Website $website
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @return Session[]
     * @throws Exception
     */
    public function findAllByWebsiteAndDates(Website $website, DateTime $startDate, DateTime $endDate = NULL)
    {
        $startDateToString = $startDate->format('Y-m-d') . ' 00:00:00';
        $endDateToString = $endDate ? $endDate->format('Y-m-d') . ' 23:59:59' : NULL;
        $startDate = new DateTime($startDateToString);
        $endDate = $endDateToString ? new DateTime($endDateToString) : NULL;

        $statement = $this->createQueryBuilder('s')
            ->andWhere('s.website = :website');

        if ($endDate) {
            $statement->andWhere('s.createdAt >= :startDate')
                ->andWhere('s.createdAt <= :endDate')
                ->setParameter('endDate', $endDate);
        } else {
            $statement->andWhere('s.createdAt < :startDate');
        }

        return $statement->setParameter('website', $website)
            ->setParameter('startDate', $startDate)
            ->orderBy('s.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get Session[] by Website group by days
     *
     * @param Website $website
     * @param DateTime $startDate
     * @param DateTime|null $endDate
     * @param array $sessions
     * @return array
     * @throws Exception
     */
    public function findWebsiteData(Website $website, DateTime $startDate, DateTime $endDate = NULL, array $sessions = [])
    {
        $sessions = $sessions ? $sessions : $this->findAllByWebsiteAndDates($website, $startDate, $endDate);
        $data = [];

        $data['all'] = $sessions;

        foreach ($sessions as $session) {

            $dateTime = $session->getCreatedAt();
            $screen = $session->getScreen();
            $city = $session->getGroup()->getCity();
            $cityName = $city->getName() ? $city->getName() : 'undefined';
            $country = $city->getCountry();
            $countryName = $country->getName() ? $country->getName() : 'undefined';
            $countryLatitude = $country->getLatitude() ? $country->getLatitude() : 'undefined';
            $countryLongitude = $country->getLongitude() ? $country->getLongitude() : 'undefined';
            $iso = $country->getIsoCode() ? $country->getIsoCode() : 'undefined';

            $data['cities'][$cityName][] = $session;
            $data['countries'][$countryName]['latitude'] = $countryLatitude;
            $data['countries'][$countryName]['longitude'] = $countryLongitude;
            $data['countries'][$countryName]['iso'] = $iso;
            $data['countries'][$countryName]['sessions'][] = $session;
            $data['screens'][$screen][] = $session;
            $data['years'][$dateTime->format('Y')][] = $session;
            $data['yearsMonths'][$dateTime->format('Y-m')][] = $session;
            $data['yearsMonthsGroup'][$dateTime->format('Y')][$dateTime->format('m')][] = $session;
            $data['days'][$session->getDay()][] = $session;
            $data['hours'][$session->getDay()][$dateTime->format('H:m')][] = $session;

            ksort($data['yearsMonthsGroup'][$dateTime->format('Y')]);
            ksort($data['hours'][$session->getDay()]);

            foreach ($session->getUrls() as $key => $url) {

                $count = !empty($data['pages'][$url->getUri()]['count'])
                    ? $data['pages'][$url->getUri()]['count'] + 1 : 1;
                $data['pages'][$url->getUri()]['count'] = $count;
                $data['pages'][$url->getUri()]['sessions'][] = $session;

                if ($url->getRefererUri() && $url->getUri()) {
                    $count = !empty($data['pages']['referer'][$url->getUri()][$url->getRefererUri()])
                        ? $data['pages']['referer'][$url->getUri()][$url->getRefererUri()] + 1 : 1;
                    $data['pages']['referer'][$url->getUri()][$url->getRefererUri()] = $count;
                }
            }
        }

        ksort($data);

        foreach ($data as $keyName => $values) {
            ksort($data[$keyName]);
        }

        return $data;
    }

    /**
     * Get Session[] by Day and Website
     *
     * @param Website $website
     * @param string $day
     * @return Session[]
     * @throws Exception
     */
    public function findByDayAndWebsite(Website $website, string $day = NULL)
    {
        if (!$day) {
            $date = new DateTime('now');
            $day = $date->format('Y-m-d');
        }

        return $this->createQueryBuilder('s')
            ->andWhere('s.day = :day')
            ->andWhere('s.website = :website')
            ->setParameter('day', $day)
            ->setParameter('website', $website)
            ->orderBy('s.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get actives Session[]
     *
     * @param Website $website
     * @return Session[]
     * @throws Exception
     */
    public function findActives(Website $website)
    {
        $delay = new \DateTime();
        $delay->setTimestamp(strtotime('2 minutes ago'));

        return $this->createQueryBuilder('s')
            ->andWhere('s.website = :website')
            ->andWhere('s.lastActivity > :delay')
            ->setParameter('website', $website)
            ->setParameter('delay', $delay)
            ->getQuery()
            ->getResult();
    }
}
