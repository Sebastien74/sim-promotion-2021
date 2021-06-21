<?php

namespace App\Twig\Content;

use App\Entity\Api\Api;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * GdprRuntime
 *
 * @property Request $request
 * @property Environment $twig
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class GdprRuntime implements RuntimeExtensionInterface
{
    private $request;
    private $twig;

    /**
     * GdprRuntime constructor.
     *
     * @param RequestStack $requestStack
     * @param Environment $twig
     */
    public function __construct(RequestStack $requestStack, Environment $twig)
    {
        $this->request = $requestStack->getMasterRequest();
        $this->twig = $twig;
    }

    /**
     * Get Cookies
     *
     * @return array
     */
    public function cookies()
    {
        $cookies = [];
        $cookiesRequest = $this->request->cookies->get('felixCookies');
        $serializer = new Serializer([new GetSetMethodNormalizer()], ['json' => new JsonEncoder()]);

        if (!empty($cookiesRequest)) {
            $cookiesRequest = $serializer->decode($cookiesRequest, 'json');
            foreach ($cookiesRequest as $cookie) {
                $cookies[$cookie['slug']] = $cookie['status'];
            }
        }

        return $cookies;
    }

    /**
     * Get Cookie by name
     *
     * @param string $name
     * @return boolean
     */
    public function cookie(string $name)
    {
        $cookies = $this->cookies();
        return isset($cookies[$name]) ? $cookies[$name] : false;
    }

    /**
     * Set iframe
     *
     * @param string $iframeCode
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function iframe(string $iframeCode)
    {
        $arguments['prototype'] = $this->twig->render('gdpr/services/iframe-prototype.html.twig', ['iframeCode' => $iframeCode]);
        $arguments['prototype_placeholder'] = $this->twig->render('gdpr/services/iframe-prototype-placeholder.html.twig', ['iframeCode' => $iframeCode]);
        echo $this->twig->render('gdpr/services/iframe.html.twig', $arguments);
    }

    /**
     * Set addThis
     *
     * @param array $api
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function addThis(array $api)
    {
        $arguments['api'] = $api;
        $arguments['prototype'] = $this->twig->render('gdpr/services/add-this-prototype.html.twig', ['api' => $api]);
        $arguments['prototype_placeholder'] = $this->twig->render('gdpr/services/add-this-prototype-placeholder.html.twig', ['api' => $api]);
        echo $this->twig->render('gdpr/services/add-this.html.twig', $arguments);
    }

    /**
     * Set tawkTo
     *
     * @param array $api
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function tawkTo(array $api)
    {
        if ($api['tawkToId'] && !$this->cookie('tawk-to')) {
            echo $this->twig->render('gdpr/services/tawk-to.html.twig');
        }
    }
}