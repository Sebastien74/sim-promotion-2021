<?php

namespace App\Service\Content;

use App\Entity\Module\Agenda\Agenda;

/**
 * AgendaService
 *
 * To manage Agenda
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class AgendaService
{
    /**
     * Get all period[] data of an Agenda
     *
     * @param Agenda $agenda
     * @return array
     */
    public function eventsDaysData(Agenda $agenda)
    {
        $currentDay = new \DateTime('now');
        $interval = new \DateInterval('P1D');

        $response['eventsDaysData'] = [];

        foreach ($agenda->getPeriods() as $period) {

            $realEnd = $period->getPublicationEnd();
            $datePeriod = new \DatePeriod($period->getPublicationStart(), $interval, $realEnd);

            foreach ($datePeriod as $date) {
                $response['eventsDaysData'][$date->format('Y-m-d')] = $period->getId();
                if ($date->format('Y-m-d') === $currentDay->format('Y-m-d')) {
                    $response['period'] = $period;
                }
            }
        }

        return $response;
    }
}