<?php

namespace App\Controller\Front\Action;

use App\Controller\Front\FrontController;
use App\Entity\Module\Table\Table;
use App\Entity\Core\Website;
use App\Entity\Layout\Block;
use App\Repository\Module\Table\TableRepository;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * TableController
 *
 * Front Table renders
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TableController extends FrontController
{
    /**
     * View
     *
     * @Route("/front/table/view/{filter}", methods={"GET"}, name="front_table_view", schemes={"%protocol%"})
     *
     * @param Request $request
     * @param TableRepository $tableRepository
     * @param Website $website
     * @param Block|null $block
     * @param null|string|int $filter
     * @return Response
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function view(Request $request, TableRepository $tableRepository, Website $website, Block $block = NULL, $filter = NULL)
    {
        if (!$filter) {
            return new Response();
        }

        /** @var Table $table */
        $table = $tableRepository->findOneByFilter($website, $filter);

        if (!$table) {
            return new Response();
        }

        $configuration = $website->getConfiguration();
        $template = $configuration->getTemplate();
        $entity = $block instanceof Block ? $block : $table;
        $entity->setUpdatedAt($table->getUpdatedAt());

        return $this->cache('front/' . $template . '/actions/table/view.html.twig', $entity, [
            'websiteTemplate' => $template,
            'website' => $website,
            'table' => $table
        ], $configuration->getFullCache());
    }
}