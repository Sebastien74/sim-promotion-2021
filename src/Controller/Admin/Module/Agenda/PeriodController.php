<?php

namespace App\Controller\Admin\Module\Agenda;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Agenda\Agenda;
use App\Entity\Module\Agenda\Period;
use App\Form\Type\Module\Agenda\PeriodType;
use App\Service\Content\AgendaService;
use DateTime;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * AgendaController
 *
 * Period Agenda Action management
 *
 * @Route("/admin-%security_token%/{website}/agendas/periods", schemes={"%protocol%"})
 * @IsGranted("ROLE_AGENDA")
 *
 * @property Period $class
 * @property PeriodType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class PeriodController extends AdminController
{
    protected $class = Period::class;
    protected $formType = PeriodType::class;

    /**
     * New Period
     *
     * @Route("/{agenda}/new/{date}", methods={"GET", "POST"}, name="admin_agendaperiod_new", options={"expose"=true})
     *
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function new(Request $request)
    {
        $website = $this->getWebsite($request);
        $agenda = $this->entityManager->getRepository(Agenda::class)->find($request->get('agenda'));

        $this->template = 'admin/page/agenda/period-modal.html.twig';
        $this->arguments['event_start_date'] = $request->get('date');
        $this->arguments['agenda'] = $agenda;
        $this->entity = new Period();
        $this->entity->setAgenda($agenda);
        $this->entity->setPublicationStart(new DateTime($this->arguments['event_start_date'] . '00:00:00'));
        $this->entity->setPublicationEnd(new DateTime($this->arguments['event_start_date'] . 'T23:59:00'));
        $this->arguments['form_route'] = $this->generateUrl('admin_agendaperiod_new', [
            'website' => $website->getId(),
            'agenda' => $agenda->getId(),
            'date' => $this->arguments['event_start_date']
        ]);

        return parent::new($request);
    }

    /**
     * Edit Item Period
     *
     * @Route("/{agenda}/edit-item/{period}", methods={"GET", "POST"}, name="admin_agendaperiod_edit_item", options={"expose"=true})
     *
     * @param Request $request
     * @param Agenda $agenda
     * @param Period $period
     * @return Response
     */
    public function editItem(Request $request, Agenda $agenda, Period $period)
    {
        $this->template = 'admin/page/agenda/period-modal.html.twig';

        if (!$agenda instanceof Agenda) {
            throw $this->createNotFoundException(sprintf("Aucune entité trouvée !!"));
        }

        $this->arguments['agenda'] = $agenda;
        $this->entity = $period;
        $this->arguments['form_route'] = $this->generateUrl('admin_agendaperiod_edit_item', [
            'website' => $agenda->getWebsite()->getId(),
            'agenda' => $agenda->getId(),
            'period' => $period->getId()
        ]);

        return parent::edit($request);
    }

    /**
     * Edit Period
     *
     * @Route("/{agenda}/edit", methods={"GET", "POST"}, name="admin_agendaperiod_edit")
     *
     * {@inheritdoc}
     * @throws Exception
     */
    public function edit(Request $request)
    {
        $this->template = 'admin/page/agenda/calendar.html.twig';
        $agenda = $this->entityManager->getRepository(Agenda::class)->find($request->get('agenda'));
        $agendaService = $this->subscriber->get(AgendaService::class);

        if (!$agenda instanceof Agenda) {
            throw $this->createNotFoundException(sprintf("Aucune entité trouvée !!"));
        }

        $this->arguments['agenda'] = $agenda;
        $this->arguments['entities'] = $this->entityManager->getRepository($this->class)->findBy([
            'agenda' => $agenda->getId()
        ]);

        $this->arguments = array_merge($this->arguments, $agendaService->eventsDaysData($agenda));

        return parent::edit($request);
    }

    /**
     * Delete Period
     *
     * @Route("/delete/{agendaperiod}", methods={"DELETE"}, name="admin_agendaperiod_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}