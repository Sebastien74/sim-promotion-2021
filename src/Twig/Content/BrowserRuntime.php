<?php

namespace App\Twig\Content;

use App\Service\Content\BrowserDetection;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * BrowserRuntime
 *
 * @property BrowserDetection $browserDetection
 * @property string $userAgent
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class BrowserRuntime implements RuntimeExtensionInterface
{
    private $browserDetection;
    private $userAgent;

    /**
     * DeviceRuntime constructor.
     *
     * @param BrowserDetection $browserDetection
     */
    public function __construct(BrowserDetection $browserDetection)
    {
        $this->browserDetection = $browserDetection;
    }

    /**
     * Get current Browser
     *
     * @return string
     */
    public function browser()
    {
        return $this->browserDetection->getBrowser();
    }

    /**
     * Get current screen
     *
     * @return string
     */
    public function screen()
    {
        if ($this->isMobile()) {
            return 'mobile';
        } elseif ($this->isTablet()) {
            return 'tablet';
        } elseif ($this->isDesktop()) {
            return 'desktop';
        }
    }

    /**
     * Check if is desktop
     *
     * @return string
     */
    public function isDesktop()
    {
        return !$this->browserDetection->isTablet() && !$this->browserDetection->isMobile();
    }

    /**
     * Check if is tablet
     *
     * @return string
     */
    public function isTablet()
    {
        return $this->browserDetection->isTablet();
    }

    /**
     * Check if is mobile
     *
     * @return string
     */
    public function isMobile()
    {
        return !$this->browserDetection->isTablet() && $this->browserDetection->isMobile();
    }

    /**
     * Check if is Firefox Browser
     *
     * @param string|null $userAgent
     * @return string
     */
    public function isFirefox(string $userAgent = NULL)
    {
        $this->setUserAgent($userAgent);
        return $this->browserDetection->is('Firefox', $this->userAgent);
    }

    /**
     * Check if is Chrome Browser
     *
     * @param string|null $userAgent
     * @return string
     */
    public function isChrome(string $userAgent = NULL)
    {
        $this->setUserAgent($userAgent);
        return $this->browserDetection->is('Chrome', $this->userAgent);
    }

    /**
     * Check if is Safari Browser
     *
     * @param string|null $userAgent
     * @return string
     */
    public function isSafari(string $userAgent = NULL)
    {
        $this->setUserAgent($userAgent);

        if ($this->isChrome($userAgent)) {
            return false;
        }

        return $this->browserDetection->is('Safari', $this->userAgent);
    }

    /**
     * Check if is Edge Browser
     *
     * @param string|null $userAgent
     * @return string
     */
    public function isEdge(string $userAgent = NULL)
    {
        $this->setUserAgent($userAgent);
        return $this->browserDetection->is('Edge', $this->userAgent);
    }

    /**
     * Check if is IE Browser
     *
     * @param string|null $userAgent
     * @return string
     */
    public function isIE(string $userAgent = NULL)
    {
        $this->setUserAgent($userAgent);
        return $this->browserDetection->is('IE', $this->userAgent);
    }

    /**
     * Check if is Opera Browser
     *
     * @param string|null $userAgent
     * @return string
     */
    public function isOpera(string $userAgent = NULL)
    {
        $this->setUserAgent($userAgent);
        return $this->browserDetection->is('Opera', $this->userAgent);
    }

    /**
     * Set User Agent
     *
     * @param string|null $userAgent
     */
    private function setUserAgent(string $userAgent = NULL)
    {
        $this->userAgent = $userAgent ? $userAgent : (!empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : NULL);
    }
}