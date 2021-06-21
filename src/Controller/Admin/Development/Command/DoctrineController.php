<?php

namespace App\Controller\Admin\Development\Command;

use App\Command\DoctrineCommand;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * DoctrineController
 *
 * To execute doctrine commands
 *
 * @Route("/admin-%security_token%/development/commands/doctrine", schemes={"%protocol%"})
 * @IsGranted("ROLE_INTERNAL")
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class DoctrineController extends BaseCommand
{
    /**
     * Update DB
     *
     * @Route("/update", methods={"GET"}, name="doctrine_schema_update")
     *
     * @param Request $request
     * @param DoctrineCommand $cmd
     * @return RedirectResponse
     * @throws Exception
     */
    public function update(Request $request, DoctrineCommand $cmd)
    {
        $this->setFlashBag($cmd->update(), 'doctrine:schema:update');
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * Clear cache result
     *
     * @Route("/clear/cache/result", methods={"GET"}, name="doctrine_clear_cache_result")
     *
     * @param Request $request
     * @param DoctrineCommand $cmd
     * @return RedirectResponse
     * @throws Exception
     */
    public function cacheClearResult(Request $request, DoctrineCommand $cmd)
    {
        $this->setFlashBag($cmd->cacheClearResult(), 'doctrine:cache:clear-result');
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * Schema validation
     *
     * @Route("/validate", methods={"GET"}, name="doctrine_schema_validate")
     *
     * @param Request $request
     * @param DoctrineCommand $cmd
     * @return RedirectResponse
     * @throws Exception
     */
    public function validate(Request $request, DoctrineCommand $cmd)
    {
        $this->setFlashBag($cmd->validate(), 'doctrine:schema:validate');
        return $this->redirect($request->headers->get('referer'));
    }
}