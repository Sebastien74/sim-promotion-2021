<?php

namespace App\Controller\Front\Action;

use App\Controller\Front\FrontController;
use App\Entity\Module\Agenda\Agenda;
use App\Entity\Core\Website;
use App\Entity\Layout\Block;
use App\Repository\Module\Agenda\AgendaRepository;
use App\Repository\Module\Agenda\PeriodRepository;
use App\Service\Content\AgendaService;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * AgendaController
 *
 * Front Agenda renders
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class AgendaController extends FrontController
{
    /**
     * To display agenda
     *
     * @param AgendaService $agendaService
     * @param AgendaRepository $agendaRepository
     * @param Website $website
     * @param Block|null $block
     * @param null $filter
     * @return string|JsonResponse|Response
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function view(AgendaService $agendaService, AgendaRepository $agendaRepository, Website $website, Block $block = NULL, $filter = NULL)
    {
        if (!$filter) {
            return new Response();
        }

        /** @var Agenda $agenda */
        $agenda = $agendaRepository->findOneByFilter($filter);
        $configuration = $website->getConfiguration();
        $websiteTemplate = $configuration->getTemplate();

        if (!$agenda) {
            return new Response();
        }

        $entity = $block instanceof Block ? $block : $agenda;
        $entity->setUpdatedAt($agenda->getUpdatedAt());

        return $this->cache('front/' . $websiteTemplate . '/actions/agenda/view.html.twig', $block, array_merge($agendaService->eventsDaysData($agenda), [
            'periodDate' => new DateTime('now'),
            'agenda' => $agenda,
            'configuration' => $configuration,
            'websiteTemplate' => $websiteTemplate,
            'website' => $website
        ]));
    }

    /**
     * Get Period
     *
     * @Route("/front/agenda/period/{agenda}/{date}", methods={"GET"}, name="front_agenda_period", schemes={"%protocol%"}, options={"expose"=true})
     *
     * @param Request $request
     * @param PeriodRepository $periodRepository
     * @param AgendaService $agendaService
     * @param Agenda $agenda
     * @param string $date
     * @return JsonResponse
     * @throws Exception
     */
    public function period(Request $request, PeriodRepository $periodRepository, AgendaService $agendaService, Agenda $agenda, string $date): JsonResponse
    {
        $website = $agenda->getWebsite();
        $configuration = $website->getConfiguration();
        $websiteTemplate = $configuration->getTemplate();
        $period = $request->get('period') ? $periodRepository->find($request->get('period')) : NULL;

        return new JsonResponse([
            'success' => true,
            'html' => $this->renderView('front/' . $websiteTemplate . '/actions/agenda/view.html.twig', array_merge($agendaService->eventsDaysData($agenda), [
                'periodDate' => new DateTime($date),
                'agenda' => $agenda,
                'period' => $period,
                'website' => $website,
                'websiteTemplate' => $websiteTemplate,
            ]))]);
    }
}