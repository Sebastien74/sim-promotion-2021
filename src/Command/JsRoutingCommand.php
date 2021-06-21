<?php

namespace App\Command;

use Symfony\Component\Filesystem\Filesystem;

/**
 * JsRoutingCommand
 *
 * To execute fos js-routing commands
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class JsRoutingCommand extends BaseCommand
{
    /**
     * Execute fos:js-routing:dump
     *
     * @param string|null $filename
     * @param bool $all
     * @return string $output->fetch()
     */
    public function dump(string $filename = NULL, bool $all = false): string
    {
        $output = $this->execute([
            'command' => 'fos:js-routing:dump',
            '--format' => 'json',
            '--target' => 'js/fos_js_routes.json',
        ]);

        if ($filename) {
            $this->generateFile($filename, $all);
        } else {
            $this->generateFile('fos_js_routes_front', false);
            $this->generateFile('fos_js_routes_admin', true);
        }

        return $output;
    }

    /**
     * Execute fos:js-routing:debug
     *
     * @return string $output->fetch()
     */
    public function debug(): string
    {
        return $this->execute([
            'command' => 'fos:js-routing:debug'
        ]);
    }

    /**
     * To generate front json file
     *
     * @param string|null $filename
     * @param bool $all
     */
    private function generateFile(string $filename = NULL, bool $all = false)
    {
        $filename = $filename ? $filename : 'fos_js_routes_front';
        $adminAllowed = ['admin_user_switcher'];
        $jsRoutingDirname = $this->kernel->getProjectDir() . '/public/js/fosjsrouting/';
        $jsRoutingDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $jsRoutingDirname);
        $jsRoutingFileDirname = $this->kernel->getProjectDir() . '/public/js/fos_js_routes.json';
        $jsRoutingFileDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $jsRoutingFileDirname);
        $filesystem = new Filesystem();

        if (!$filesystem->exists($jsRoutingDirname)) {
            $filesystem->mkdir($jsRoutingDirname, 0777);
        }

        if ($filesystem->exists($jsRoutingFileDirname)) {

            $content = json_decode(file_get_contents($jsRoutingFileDirname));
            foreach ($content->routes as $routeName => $params) {
                if ($all || in_array($routeName, $adminAllowed) || !preg_match('/admin_/', $routeName) && !preg_match('/cache_clear/', $routeName)) {
                    $routes['routes'][$routeName] = $params;
                }
            }

            $routes['base_url'] = $content->base_url;
            $routes['routes'] = (object)$routes['routes'];
            $routes['prefix'] = $content->prefix;
            $routes['host'] = $content->host;
            $routes['port'] = $content->port;
            $routes['port'] = $content->port;
            $routes['scheme'] = $content->scheme;
            $routes['locale'] = $content->locale;

            file_put_contents($jsRoutingDirname . $filename . '.json', json_encode((object)$routes));
        }
    }
}