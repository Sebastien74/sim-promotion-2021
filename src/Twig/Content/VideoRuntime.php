<?php

namespace App\Twig\Content;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * VideoRuntime
 *
 * @property Environment $twig
 * @property array $request
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class VideoRuntime implements RuntimeExtensionInterface
{
    private $twig;
    private $arguments = [];

    /**
     * VideoRuntime constructor.
     *
     * @param Environment $twig
     */
    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * Get video view
     *
     * @param string $url
     * @return string|null
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function video(string $url)
    {
        $this->arguments = [];
        $this->arguments['url'] = $url;

        if (preg_match('/youtube/', $url) || preg_match('/youtu.be/', $url)) {
            $this->getYoutube($url);
        } elseif (preg_match('/vimeo/', $url)) {
            $this->getVimeo($url);
        } elseif (preg_match('/dailymotion/', $url)) {
            $this->getDailymotion($url);
        } elseif (preg_match('/facebook/', $url)) {
            $this->getFacebook($url);
        }

        if (!empty($this->arguments['embed'])) {
            $this->arguments['prototype'] = $this->twig->render('gdpr/services/video-prototype.html.twig', $this->arguments);
            $this->arguments['prototype_placeholder'] = $this->twig->render('gdpr/services/video-prototype-placeholder.html.twig', $this->arguments);
            echo $this->twig->render('gdpr/services/video.html.twig', $this->arguments);
        }

        return NULL;
    }

    /**
     * Get Youtube arguments
     *
     * @param string $url
     */
    private function getYoutube(string $url)
    {
        $videoID = NULL;
        $this->arguments['player'] = 'youtube';

        if (preg_match('/watch/', $url)) {
            $matches = explode("&", $url);
            foreach ($matches as $match) {
                if (preg_match('/watch/', $match)) {
                    $explode = explode('watch?v=', $match);
                    $this->arguments['videoID'] = $videoID = end($explode);
                    break;
                }
            }
        } elseif (preg_match('/youtu.be/', $url)) {
            $matches = explode('/', $url);
            $this->arguments['videoID'] = $videoID = end($matches);
        }

        $this->arguments['embed'] = 'https://www.youtube.com/embed/' . $videoID;
    }

    /**
     * Get Vimeo arguments
     *
     * @param string $url
     */
    private function getVimeo(string $url)
    {
        $this->arguments['player'] = 'vimeo';
        $matches = explode("/", $url);
        $videoID = end($matches);
        $this->arguments['embed'] = "https://player.vimeo.com/video/" . $videoID;
    }

    /**
     * Get Dailymotion arguments
     *
     * @param string $url
     */
    private function getDailymotion(string $url)
    {
        $this->arguments['player'] = 'dailymotion';
        $matches = explode("/", $url);
        $videoID = end($matches);
        $this->arguments['embed'] = "https://www.dailymotion.com/embed/video/" . $videoID;
    }

    /**
     * Get Facebook arguments
     *
     * @param string $url
     */
    private function getFacebook(string $url)
    {
        $this->arguments['player'] = 'facebook';
        $this->arguments['embed'] = "https://www.facebook.com/plugins/video.php?height=314&href=" . $url . "&show_text=false&width=560";
    }
}