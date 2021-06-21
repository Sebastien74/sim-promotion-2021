<?php

namespace App\Controller\Admin\Development;

use App\Controller\Admin\AdminController;
use App\Entity\Core\Log;
use DateTime;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * LogController
 *
 * Webmaster logs
 *
 * @Route("/admin-%security_token%/development", schemes={"%protocol%"})
 * @IsGranted("ROLE_INTERNAL")
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class LogController extends AdminController
{
    /**
     * Index Logs
     *
     * @Route("/logs", methods={"GET"}, name="admin_logs")
     *
     * @return Response
     * @throws Exception
     */
    public function logs()
    {
        $this->setLogsAsRead();

        $finder = new Finder();
        $finder->files()->in($this->kernel->getLogDir());

        $keyFiles = ['dev', 'prod'];
        $dailyLogs = [];
        $logFiles = [];

        foreach ($finder as $file) {

            $explodeFilename = explode('.', $file->getFilename());
            $date = substr($explodeFilename[0], -10);
            $isDate = DateTime::createFromFormat('Y-m-d', $date) !== FALSE;

            if (in_array($explodeFilename[0], $keyFiles)) {
                $date = substr($explodeFilename[1], -10);
                $isDate = DateTime::createFromFormat('Y-m-d', $date) !== FALSE;
            }

            if ($isDate) {
                $dateFormat = abs(str_replace('-', '', $date));
                $dailyLogs[$dateFormat][$date][] = $file->getFilename();
            } else {
                $logFiles[str_replace('.log', '', $file->getFilename())] = $file->getFilename();
            }
        }

        ksort($dailyLogs);
        ksort($logFiles);

        return $this->cache('admin/page/development/logs.html.twig', [
            'dailyLogs' => array_reverse($dailyLogs),
            'dailyLogs' => array_reverse($dailyLogs),
            'logFiles' => $logFiles
        ]);
    }

    /**
     * Log file
     *
     * @Route("/{website}/log/{file}", methods={"GET"}, name="admin_log")
     *
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function log(Request $request)
    {
        $logs = [];
        $fileDir = $this->kernel->getLogDir() . '/' . $request->get('file');
        $fileSystem = new Filesystem();

        if ($fileSystem->exists($fileDir)) {

            $logsContent = file_get_contents($fileDir);
            $splitLogs = preg_split("/\\r\\n|\\r|\\n/", $logsContent);

            foreach ($splitLogs as $log) {

                $date = $this->getStringBetween($log, '[', ']');
                $log = str_replace('[' . $date . ']', '', $log);
                $matches = explode(':', $log);
                $code = $matches[0];
                $log = str_replace($code . ':', '', $log);

                if (!preg_match('/-----------------------------/', $log) && $date) {

                    $colorMatches = explode('.', $code);
                    $status = !empty($colorMatches[1]) ? strtolower($colorMatches[1]) : 'undefined';

                    $logs[] = [
                        'date' => $date ? new DateTime($date) : $date,
                        'code' => $code,
                        'status' => preg_match('/Deprecated/', $log) ? 'warning' : $status,
                        'message' => trim(htmlspecialchars_decode($log))
                    ];
                }
            }
        }

        return $this->cache('admin/page/development/log.html.twig', [
            "file" => $request->get('file'),
            "logs" => array_slice($logs, 0, 50)
        ]);
    }

    /**
     * To clear all logs
     *
     * @Route("/logs/clear", methods={"DELETE"}, name="admin_log_clear")
     */
    public function clear()
    {
        $filesystem = new Filesystem();
        $logsDirname = $this->kernel->getProjectDir() . '/var/log';
        $logsDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $logsDirname);

        if ($filesystem->exists($logsDirname)) {
            $finder = new Finder();
            $finder->in($logsDirname);
            foreach ($finder as $file) {
                try {
                    $filesystem->remove($file);
                } catch (Exception $exception) {
                }
            }
        }

        return new JsonResponse(['success' => true]);
    }

    /**
     * Get string between char
     *
     * @param string $string
     * @param string $start
     * @param string $end
     * @return false|string
     */
    private function getStringBetween(string $string, string $start, string $end)
    {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) {
            return '';
        }
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    /**
     * To set logs as read
     */
    private function setLogsAsRead()
    {
        $logs = $this->entityManager->getRepository(Log::class)->findByIsRead(false);

        foreach ($logs as $log) {
            /** @var Log $log */
            $log->setIsRead(true);
            $this->entityManager->persist($log);
            $this->entityManager->flush();
        }
    }
}