<?php

namespace App\Twig\Content;

use App\Entity\Module\Map\Address;
use App\Entity\Core\Configuration;
use App\Entity\Core\Website;
use App\Entity\Information\Phone;
use App\Service\Content\CryptService;
use App\Twig\Core\AppRuntime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * CoreRuntime
 *
 * @property Request $request
 * @property RouterInterface $router
 * @property AppRuntime $appExtension
 * @property Environment $templating
 * @property CryptService $cryptService
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CoreRuntime implements RuntimeExtensionInterface
{
    private $request;
    private $router;
    private $appExtension;
    private $templating;
    private $cryptService;

    /**
     * CoreRuntime constructor.
     *
     * @param RequestStack $requestStack
     * @param RouterInterface $router
     * @param AppRuntime $appExtension
     * @param Environment $templating
     * @param CryptService $cryptService
     */
    public function __construct(
        RequestStack $requestStack,
        RouterInterface $router,
        AppRuntime $appExtension,
        Environment $templating,
        CryptService $cryptService)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->router = $router;
        $this->appExtension = $appExtension;
        $this->templating = $templating;
        $this->cryptService = $cryptService;
    }

    /**
     * Get last route
     *
     * @return null|string
     */
    public function lastRoute()
    {
        $lastRoute = preg_match('/\/admin-' . $_ENV['SECURITY_TOKEN'] . '/', $this->request->getUri())
            ? $this->request->getSession()->get('last_route_back')
            : $this->request->getSession()->get('last_route');

        try {
            if (is_object($lastRoute) && property_exists($lastRoute, 'name') && $this->appExtension->routeExist($lastRoute->name)) {
                return $this->router->generate($lastRoute->name, $lastRoute->params);
            }
        } catch (\Exception $exception) {
            return $this->request->headers->get('referer');
        }

        return $this->request->headers->get('referer');
    }

    /**
     * Generate view for email
     *
     * @param string $email
     * @param Website $website
     * @param bool $icon
     * @param bool $entitled
     * @param array $options
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function email(string $email, Website $website, bool $icon = true, $entitled = true, $options = [])
    {
        echo $this->templating->render('core/email.html.twig', array(
            'email' => $email,
            'website' => $website,
            'icon' => $icon,
            'entitled' => $entitled,
            'options' => $options
        ));
    }

    /**
     * Generate view for phone
     *
     * @param array|Phone $phone
     * @param Website $website
     * @param bool $icon
     * @param bool $entitled
     * @param array $options
     * @return null
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function phone($phone, Website $website, bool $icon = true, bool $entitled = true, array $options = [])
    {
        if (is_string($phone)) {
            return NULL;
        }

        if (is_array($phone) && !isset($phone['id'])) {
            $data = $phone;
            $phone = new Phone();
            $phone->setTagNumber($data['link']);
            $phone->setNumber($data['label']);
        }

        if ($phone instanceof Phone && empty($phone->getType())) {
            $phone->setType('office');
        }

        echo $this->templating->render('core/phone.html.twig', array(
            'phone' => $phone,
            'website' => $website,
            'icon' => $icon,
            'entitled' => $entitled,
            'options' => $options
        ));
    }

    /**
     * Generate view for address
     *
     * @param Address|\App\Entity\Information\Address $address
     * @param Website $website
     * @param string|null $zone
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function address($address, Website $website, string $zone = NULL)
    {
        echo $this->templating->render('core/address.html.twig', array(
            'address' => $address,
            'website' => $website,
            'zone' => $zone
        ));
    }

    /**
     * Convert text for mailto link
     *
     * @param string $text
     * @return string
     */
    public function mailToBody(string $text)
    {
        $response = str_replace("\r\n", "<br>", $text);
        $response = str_replace("</p>", "</p><br>", $response);
        $response = str_replace("<br/>", "%0D%0A", $response);
        $response = str_replace("<br>", "%0D%0A", $response);
        $response = str_replace(" ", "%20", $response);

        return strip_tags($response);
    }

    /**
     * Encrypt string
     *
     * @param string $string
     * @param Website $website
     * @return string|null
     */
    public function encrypt(string $string, Website $website): ?string
    {
        return $this->cryptService->execute($website, $string, 'e');
    }

    /**
     * Decrypt string
     *
     * @param string $string
     * @param Website $website
     * @return string|null
     */
    public function decrypt(string $string, Website $website): ?string
    {
        return $this->cryptService->execute($website, $string, 'd');
    }

    /**
     * Get main pages
     *
     * @param Configuration $configuration
     * @return array
     */
    public function mainPages(Configuration $configuration): array
    {
        $pages = [];

        foreach ($configuration->getPages() as $page) {
            $pages[$page->getSlug()] = $page;
        }

        return $pages;
    }

    /**
     * Get URI infos
     *
     * @param string|null $string $string
     * @return null|array
     */
    public function uriInfos(string $string = NULL): ?array
	{
        if ($string) {

            $clean = str_replace($this->request->getSchemeAndHttpHost(), '', $string);
            $matches = explode('#', $clean);

            return [
                'uri' => $matches[0],
                'anchor' => !empty($matches[1]) ? $matches[1] : NULL
            ];
        }

        return NULL;
    }

    /**
     * Truncate string
     *
     * @param string|null $string $string
     * @param int $length
     * @return null|string
     */
    public function truncate(string $string = NULL, int $length = 30)
    {
        if ($string) {

            return substr($string, 0, $length);
        }

        return NULL;
    }

    /**
     * Convert minutes to hours
     *
     * @param int|null $time
     * @param string $format
     * @return null|string
     */
    public function minutesToHour(int $time = 0, $format = '%02d:%02d')
    {
        if ($time > 1) {
            $hours = floor($time / 60);
            $minutes = ($time % 60);
            return sprintf($format, $hours, $minutes);
        }
    }
}