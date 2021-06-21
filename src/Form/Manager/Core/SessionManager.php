<?php

namespace App\Form\Manager\Core;

use App\Entity\Core\Configuration;
use App\Entity\Core\Entity;
use App\Entity\Core\Website;
use App\Entity\Information\Information;
use App\Entity\Media\ThumbConfiguration;
use App\Entity\Seo\SeoConfiguration;
use Symfony\Component\HttpFoundation\Request;

/**
 * SessionManager
 *
 * Set main sessions
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SessionManager
{
    /**
     * Manage Session
     *
     * @param Request $request
     * @param $entity
     */
    public function execute(Request $request, $entity)
    {
        $session = $request->getSession();

		$session->remove('adminWebsite');
		$session->remove('frontWebsite');
		$session->remove('frontWebsiteObject');

        if ($entity instanceof Website) {
            $session->set('adminWebsite', $entity);
            $session->remove('configuration_' . $entity->getId());
        } elseif ($entity instanceof Entity) {
            $sessionSlug = str_replace('\\', '_', $entity->getClassName());
            $session->remove('configuration_' . $sessionSlug);
        } elseif ($entity instanceof Configuration) {
            $session->remove('configuration_' . $entity->getWebsite()->getId());
            $session->remove('social_networks_' . $entity->getWebsite()->getId());
        } elseif ($entity instanceof SeoConfiguration || $entity instanceof Information) {
            $session->remove('social_networks_' . $entity->getWebsite()->getId());
        } elseif ($entity instanceof ThumbConfiguration) {
            $session->remove('thumbs_actions_' . $entity->getConfiguration()->getWebsite()->getId());
        }
    }
}