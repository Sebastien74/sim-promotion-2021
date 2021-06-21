<?php

namespace App\Service\Core;

use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * LastRouteService
 *
 * To register last route in Session
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class LastRouteService
{
	/**
	 * To execute service
	 *
	 * @param RequestEvent $event
	 */
	public function execute(RequestEvent $event)
	{
		$request = $event->getRequest();
		$uri = $request->getUri();
		$routeName = $request->get('_route');

		if ($this->isAllowed($routeName, $uri)) {

			$session = $request->getSession();

			$routeParams = $request->get('_route_params');
			if ($routeName[0] == '_') {
				return;
			}

			$routeData = (object)['name' => $routeName, 'params' => $routeParams];

			/** Do not save same matched route twice */
			$thisRoute = $session->get('this_route', []);
			if ($thisRoute == $routeData) {
				return;
			}

			$session->set('last_uri', $uri);
			$session->set('last_route', $thisRoute);
			$session->set('this_route', $routeData);

			if ($routeName !== 'security_front_login') {
				$session->set('previous_secure_url', $uri);
			}

			if (preg_match('/\/admin-' . $_ENV['SECURITY_TOKEN'] . '/', $uri) && is_object($thisRoute) && preg_match('/admin_/', $thisRoute->name)) {
				$session->set('last_route_back', $thisRoute);
				$session->set('this_route_back', $routeData);
			}

			if (preg_match('/front/', $uri) && is_object($thisRoute) && !preg_match('/admin_/', $thisRoute->name)) {
				$session->set('last_uri_front', $uri);
				$session->set('last_route_front', $thisRoute);
				$session->set('this_route_front', $routeData);
			}
		}
	}

	/**
	 * Check if route is allowed to register in session
	 *
	 * @param string|null $routeName
	 * @param string|null $uri
	 * @return bool
	 */
	private function isAllowed(string $routeName = NULL, string $uri = NULL): bool
	{
		if(!$routeName) {
			return false;
		}

		$disabledRoutes = [
			'liip_imagine_filter',
			'fos_js_routing_js',
			'admin_code_generator',
			'admin_mediarelation_reset_media',
			'admin_zone_size',
			'admin_zone_background',
			'admin_col_align',
			'admin_col_background',
			'admin_col_size',
			'admin_cols_positions',
			'admin_block_add',
			'admin_blocks_positions',
			'front_gdpr_scripts',
			'front_webmaster_toolbox'
		];

		if (in_array($routeName, $disabledRoutes)) {
			return false;
		}

		$disabledUris = [
			'ajax',
			'remove',
			'duplicate',
			'modal',
			'delete',
			'reset',
			'front\/crypt',
			'urls\/status',
			'thumbnails\/media',
			'uploads\/',
			'webp',
			'png',
			'jpeg',
			'jpg',
			'gif',
			'position'
		];

		foreach ($disabledUris as $disabledUri) {
			if (preg_match('/' . $disabledUri . '/', $uri)) {
				return false;
			}
		}

		$adminPatterns = ['edit', 'tree', 'index', 'layout'];
		$registerAdmin = false;
		foreach ($adminPatterns as $pattern) {
			if (preg_match('/' . $pattern . '/', $uri)) {
				$registerAdmin = true;
				break;
			}
		}

		if (preg_match('/\/admin-' . $_ENV['SECURITY_TOKEN'] . '/', $uri) && !$registerAdmin) {
			return false;
		}

		return true;
	}
}