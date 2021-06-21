<?php

namespace App\Twig\Content;

use App\Entity\Core\Icon;
use App\Twig\Core\AppRuntime;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * IconRuntime
 *
 * @property KernelInterface $kernel
 * @property AppRuntime $appExtension
 * @property Filesystem $fileSystem
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class IconRuntime implements RuntimeExtensionInterface
{
    private $kernel;
    private $appExtension;
    private $fileSystem;

    /**
     * IconRuntime constructor.
     *
     * @param KernelInterface $kernel
     * @param AppRuntime $appExtension
     */
    public function __construct(KernelInterface $kernel, AppRuntime $appExtension)
    {
        $this->kernel = $kernel;
        $this->appExtension = $appExtension;
        $this->fileSystem = new Filesystem();
    }

    /**
     * Get icon by path
     *
     * @param string|null $library
     * @param string|null $filename
     * @param array $options
     * @return string
     */
    public function iconPath(string $library = NULL, string $filename = NULL, array $options = []): ?string
    {
        return $this->iconHtml('/medias/icons/' . $library . '/' . $filename, $options);
    }

    /**
     * Get fontawesome icon
     *
     * @param string $icon
     * @param int|null $width
     * @param int|null $height
     * @param string|null $class
     * @param string|null $fill
     * @param bool $echo
     * @return string
     */
    public function fontawesome(
        string $icon,
        int $width = NULL,
        int $height = NULL,
        string $class = NULL,
        string $fill = NULL,
        bool $echo = true): ?string
    {
        $matches = explode(' ', $icon);
        $category = str_replace(['fab', 'fad', 'fal', 'far', 'fas'], ['brands', 'duotone', 'light', 'regular', 'solid'], $matches[0]);

        if (!empty($matches[1])) {

            $filenameMatches = explode('-', $matches[1]);
            unset($filenameMatches[0]);
            $filename = '';
            foreach ($filenameMatches as $match) {
                $filename .= '-' . $match;
            }

            return $this->iconHtml('/fontawesome/' . $category . '/' . ltrim($filename, '-') . '.svg', [
                'width' => $width,
                'height' => $height,
                'fill' => $fill,
                'class' => $class
            ], $echo);
        }

        return NULL;
    }

    /**
     * Get icon content
     *
     * @param string $iconPath
     * @param array $options
     * @param bool $echo
     * @return string|null
     */
    public function iconHtml(string $iconPath, array $options = [], bool $echo = true): ?string
    {
        $iconPath = str_replace(['medias/icons', 'medias\\icons'], '', $iconPath);
        $dirname = $this->kernel->getProjectDir() . '/public/medias/icons/' . ltrim($iconPath, '/, \\');
        $dirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $dirname);

        if ($this->fileSystem->exists($dirname)) {

            $svg = file_get_contents($dirname);

            preg_match_all('/id="([^"]*)"/', $svg, $matches);
            if (!empty($matches) && empty($options['id'])) {
                foreach ($matches as $key => $svgMatches) {
                    foreach ($svgMatches as $idAttribute) {
                        preg_match('/id="([^"]*)"/', $idAttribute, $matchesId);
                        if (isset($matchesId[1])) {
                            $svg = str_replace($matchesId[1], $this->appExtension->charsId(), $svg);
                        }
                    }
                }
            }

            $attributes = ['id', 'width', 'height', 'fill', 'class'];
            $svg = str_replace('\'', '"', $svg);
            foreach ($attributes as $attribute) {
                if (isset($options[$attribute])) {
                    $svg = preg_replace('!\\s+' . $attribute . '=("|\')?[-_():;a-z0-9 ]+("|\')?!i', '', strval($svg));
                    $svg = preg_replace('/(<svg\b[^><]*)>/i', '$1 ' . $attribute . '="' . $options[$attribute] . '">', $svg);
                }
            }

            if ($echo) {
                echo preg_replace('/<!(?!<!)[^\[>].*?>/', '', $svg);
            } else {
                return preg_replace('/<!(?!<!)[^\[>].*?>/', '', $svg);
            }
        }

        return NULL;
    }
}