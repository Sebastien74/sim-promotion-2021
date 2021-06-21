<?php

namespace App\Controller\Admin\Seo;

use App\Controller\Admin\AdminController;
use App\Entity\Seo\Session;
use App\Service\Admin\AnalyticService;
use DateInterval;
use DateTime;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * AnalyticsController
 *
 * Google Analytics charts views
 *
 * @Route("/admin-%security_token%/{website}/seo/analytics", schemes={"%protocol%"})
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class AnalyticsController extends AdminController
{
    /**
     * Google Analytics render
     *
     * @Route("/google/{page}/{locale}", methods={"GET"}, name="admin_google_analytics")
     * @IsGranted("ROLE_GOOGLE_ANALYTICS")
     *
     * @param Request $request
     * @param string $page
     * @param string $locale
     * @return Response
     */
    public function googleAnalytics(Request $request, string $page, string $locale)
    {
        $website = $this->getWebsite($request);

        return $this->render('admin/page/seo/google-analytics/' . $page . '.html.twig', [
            'page' => $page,
            'locale' => $locale,
            'website' => $website,
            'api' => $website->getApi()
        ]);
    }

    /**
     * Analytics render
     *
     * @Route("/cms/dashboard", methods={"GET"}, name="admin_analytics_dashboard")
     * @IsGranted("ROLE_ANALYTICS")
     * @IsGranted("ROLE_INTERNAL")
     *
     * @param Request $request
     * @param AnalyticService $analyticService
     * @return Response
     * @throws Exception
     */
    public function analytics(Request $request, AnalyticService $analyticService)
    {
        $dates = $this->getDates($request);
        $template = 'admin/page/seo/analytics/view.html.twig';
        $cache = $analyticService->getCache($this->getWebsite($request), $dates['startDate'], $dates['endDate']);
        $arguments = [
            'xKey' => $dates['xKey'],
            'duration' => $dates['duration'],
            'startDate' => $dates['startDate'],
            'endDate' => $dates['endDate'],
            'currentYear' => $dates['currentYear'],
            'actives' => $this->entityManager->getRepository(Session::class)->findActives($this->getWebsite($request)),
            'data' => !empty($cache['sessions']) ? $cache['sessions'] : [],
            'others' => !empty($cache['others']) ? $cache['others'] : [],
            'users' => !empty($cache['users']) ? $cache['users'] : [],
            'extraData' => !empty($cache['extraData']) ? $cache['extraData'] : [],
            'daysInMonths' => $dates['daysInMonths']
        ];

        if ($request->get('ajax')) {
            return new JsonResponse(['html' => $this->renderView($template, $arguments)]);
        }

        return $this->render($template, $arguments);
    }

    /**
     * To generate Analytics cache
     *
     * @Route("/cms/genrate/yaml-cache/{startDate}/{endDate}", methods={"GET"}, name="admin_analytics_cache", options={"expose"=true})
     * @IsGranted({"ROLE_ANALYTICS", "ROLE_INTERNAL"})
     *
     * @param Request $request
     * @param AnalyticService $analyticService
     * @param string $startDate
     * @param string $endDate
     * @return JsonResponse
     */
    public function generateCache(Request $request, AnalyticService $analyticService, string $startDate, string $endDate)
    {
        $analyticService->generateCache($this->getWebsite($request), new DateTime($startDate), new DateTime($endDate));
        return new JsonResponse(['sussess' => true]);
    }

    /**
     * Get form date
     *
     * @param Request $request
     * @return array
     * @throws Exception
     */
    private function getDates(Request $request)
    {
        $day = $request->get('day');
        $currentDate = new DateTime('now');

        if ($request->get('year')) {
            $startDate = new DateTime($request->get('year') . '-01-01 00:00:00');
            $endDate = new DateTime($request->get('year') . '-12-31 23:59:59');
            $duration = $this->getDaysInYear($startDate->format('Y')) - 1;
        } else {
            $duration = $request->get('duration') ? $request->get('duration') : 6;
            $startDateReferer = $request->get('start') ? new DateTime($request->get('start')) : new DateTime('now');
            $startDate = $startDateReferer->sub(new DateInterval('P' . $duration . 'D'));
            $endDate = $request->get('end') ? new DateTime($request->get('end')) : new DateTime('now');
        }

        if ($day && $day === 'yesterday') {
            $startDate = $endDate = $currentDate->sub(new DateInterval('P1D'));
            $duration = 1;
        } elseif ($day && $day === 'today') {
            $startDate = $endDate = $currentDate;
            $duration = 1;
        }

        return [
            'xKey' => $duration === 1 ? 'hour' : 'month',
            'format' => $duration === 1 ? 'H:i' : 'yyyy-mm-dd',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'daysInMonths' => cal_days_in_month(CAL_GREGORIAN, $startDate->format('m'), $startDate->format('Y')),
            'currentYear' => intval($currentDate->format('Y')),
            'duration' => $duration === 1 ? $duration : $duration + 1,
        ];
    }

    /**
     * Get numbers of days
     * @param string $year
     * @return int
     */
    function getDaysInYear(string $year)
    {
        $days = 0;
        for ($month = 1; $month <= 12; $month++) {
            $days = $days + cal_days_in_month(CAL_GREGORIAN, $month, $year);
        }
        return $days;
    }
}