<?php

namespace App\Service\Content;

use App\Entity\Core\Website;
use App\Entity\Media\Media;
use App\Entity\Media\Thumb;
use App\Entity\Security\User;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * ImageThumbnail
 *
 * Manage image crop
 *
 * @property array ALLOWED_EXTENSIONS
 * @property array ALLOWED_WEBP_EXTENSIONS
 * @property array PC_SIZES
 * @property array RETINA_SIZES
 * @property array SIZES
 *
 * @property KernelInterface $kernel
 * @property Request $request
 * @property array $sizes
 * @property string $projectDirname
 * @property Filesystem $filesystem
 * @property User $user
 * @property string $extension
 * @property string $websiteDirectory
 * @property string $thumbDirname
 * @property string $mediaDirname
 * @property string $lazyDirname
 * @property string $cropDirname
 * @property string $webpDirname
 * @property string $createFunction
 * @property string $imageFunction
 * @property int $quality
 * @property string $fileDirname
 * @property bool $forceSize
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ImageThumbnail
{
	private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png'];
	private const ALLOWED_WEBP_EXTENSIONS = ['jpg', 'jpeg'];
	private const PC_SIZES = [320, 480, 1024, 1440, 1680];
	private const RETINA_SIZES = [640, 960, 2048, 2558];
	private const MIN_WIDTH = 100;

	private $kernel;
	private $request;
	private $sizes;
	private $projectDirname;
	private $filesystem;
	private $user;
	private $extension;
	private $websiteDirectory;
	private $thumbDirname;
	private $mediaDirname;
	private $lazyDirname;
	private $cropDirname;
	private $webpDirname;
	private $createFunction;
	private $imageFunction;
	private $quality;
	private $fileDirname;
	private $forceSize;

	/**
	 * ImageThumbnail constructor.
	 *
	 * @param KernelInterface $kernel
	 * @param RequestStack $requestStack
	 */
	public function __construct(KernelInterface $kernel, RequestStack $requestStack)
	{
		$this->kernel = $kernel;
		$this->request = $requestStack->getMasterRequest();
		$this->sizes = array_merge(self::PC_SIZES, self::RETINA_SIZES);
		$this->projectDirname = $this->kernel->getProjectDir();
		$this->filesystem = new Filesystem();

		$container = $this->kernel->getContainer();
		$token = $container->get('security.token_storage')->getToken();
		$this->user = method_exists($token, 'getUser') && method_exists($token->getUser(), 'getId') ? $token->getUser() : NULL;
	}

	/**
	 * To execute service
	 *
	 * @param Media|null $media
	 * @param Thumb|null $thumb
	 * @param array $options
	 * @return array
	 */
	public function execute(Media $media = NULL, Thumb $thumb = NULL, array $options = []): array
	{
		$this->setExtension($media);

		$infos = [];

		if ($media instanceof Media && $this->extension && in_array($this->extension, self::ALLOWED_EXTENSIONS) && $media->getScreen() === 'desktop') {

			$options = $this->setOptions($options);
			$website = $media->getWebsite();
			$this->websiteDirectory = $website->getUploadDirname();

			$this->initialize($media);
			$infos = $this->generate($website, $media, $thumb, $options);
		}

		return [
			'thumb' => $thumb,
			'sizes' => !empty($infos['sizes']) ? $infos['sizes'] : [],
			'lazyFiles' => !empty($infos['lazyFiles']) ? $infos['lazyFiles'] : [],
			'files' => $this->getThumbnails($media)
		];
	}

	/**
	 * To set extension
	 *
	 * @param Media|null $media
	 */
	private function setExtension(Media $media = NULL)
	{
		if ($media instanceof Media) {

			$filename = $media->getFilename();

			if ($media->getExtension()) {
				$this->extension = $media->getExtension();
			}

			if (!$this->extension || !preg_match('/' . $this->extension . '/', $filename)) {
				$filenameMatches = explode('.', $filename);
				$this->extension = end($filenameMatches);
			}
		}
	}

	/**
	 * To set options
	 *
	 * @param array $options
	 * @return array
	 */
	private function setOptions(array $options): array
	{
		if (!empty($options['maxWidth'])) {
			$options['width'] = $options['maxWidth'];
		}

		if (!empty($options['maxHeight'])) {
			$options['height'] = $options['maxHeight'];
		}

		return $options;
	}

	/**
	 * To initialize different default stuffs
	 *
	 * @param Media $media
	 */
	private function initialize(Media $media)
	{
		$mediaDirname = $media->getId() ?: 'placeholder';

		/** Generate Website folder */
		$this->thumbDirname = $this->generateDirectory($this->projectDirname . '/public/thumbnails/' . $this->websiteDirectory);
		/** Generate Media folder */
		$this->mediaDirname = $this->generateDirectory($this->thumbDirname . '/' . $mediaDirname);
		/** Generate Lazy folder */
		$this->lazyDirname = $this->generateDirectory($this->projectDirname . '/public/thumbnails/lazy');
		/** Generate Media crop folder */
		$this->cropDirname = $this->generateDirectory($this->mediaDirname . '/crops');
		/** Generate Webp crop folder */
		$this->webpDirname = $this->generateDirectory($this->mediaDirname . '/webp');
	}

	/**
	 * To generate directory
	 *
	 * @param string $dirname
	 * @return string
	 */
	private function generateDirectory(string $dirname): string
	{
		$filesystem = new Filesystem();
		$dirname = str_replace(['/', '\\', '\\\\', '//'], DIRECTORY_SEPARATOR, $dirname);
		if (!$filesystem->exists($dirname)) {
			$filesystem->mkdir($dirname, 0777);
		}

		return $dirname;
	}

	/**
	 * To generate all screens thumbnails by Media
	 *
	 * @param Website $website
	 * @param Media $media
	 * @param Thumb|null $thumb
	 * @param array $options
	 * @return array
	 */
	private function generate(Website $website, Media $media, Thumb $thumb = NULL, array $options = []): array
	{
		$filesSizes = [];
		$lazyFiles = [];

		if ($media->getScreen() === 'desktop') {

			$websiteDirname = $this->projectDirname . '/public/uploads/' . $this->websiteDirectory . '/';
			$originalDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $websiteDirname . $media->getFilename());

			/** Set image PHP functions */
			$this->createFunction = $this->extension === 'png' ? 'imagecreatefrompng' : 'imagecreatefromjpeg';
			$this->imageFunction = $this->extension === 'png' ? 'imagepng' : 'imagejpeg';
			$this->quality = $this->extension === 'png' ? 9 : 100;

			if ($this->filesystem->exists($originalDirname) && in_array($this->extension, self::ALLOWED_EXTENSIONS)) {

				$thumbDirname = $this->thumbnailByConfiguration($media, $originalDirname, $thumb, $options);
				$dirname = $thumbDirname ?: $originalDirname;
				list($width, $height) = getimagesize($dirname);

				foreach ($this->sizes as $size) {
					$newHeight = $width >= $size ? ceil(($height * $size) / $width) : $height;
					$ratio = $newHeight / $height;
					$newWidth = ceil($width * $ratio);
					$filesSizes[$size] = $this->thumbnailFile($media, $size, $newWidth, $newHeight, $width, $height, $dirname, $thumbDirname);
					$lazyFiles[$size] = $this->lazyFile($size, $filesSizes[$size]);
				}
			}
		}

		ksort($filesSizes);
		ksort($lazyFiles);

		return [
			'sizes' => $filesSizes,
			'lazyFiles' => $lazyFiles,
		];
	}

	/**
	 * To generate thumbnail by configuration
	 *
	 * @param Media $media
	 * @param string $originalDirname
	 * @param Thumb|null $thumb
	 * @param array $options
	 * @return string|null
	 */
	private function thumbnailByConfiguration(Media $media, string $originalDirname, Thumb $thumb = NULL, array $options = []): ?string
	{
		$thumbDirname = $this->getThumbnailConfigurationDirname($media, $thumb, $options);
		list($originalWidth, $originalHeight) = getimagesize($originalDirname);

		if ($thumb && ($thumb->getWidth() || $thumb->getHeight()) || !empty($options['width']) || !empty($options['height'])) {

			/** Generate original file if sizes are too small */
			if ($thumb && ($originalWidth < $thumb->getWidth() || $originalHeight < $thumb->getHeight()) || !empty($options['width']) || !empty($options['height'])) {
				$resizeWidth = $thumb && $thumb->getWidth() ? $thumb->getWidth() : (!empty($options['width']) ? $options['width'] : $originalWidth);
				$resizeHeight = $thumb && $thumb->getHeight() ? $thumb->getHeight() : (!empty($options['height']) ? $options['height'] : $originalHeight);
				if (!empty($options['width']) || !empty($options['height'])) {
					$this->forceSize = true;
				}
				if ($thumb && $originalWidth < $thumb->getWidth() || !empty($options['width']) && $originalWidth < $options['width']) {
					$resizeHeight = ceil(($originalHeight * $resizeWidth) / $originalWidth);
				}
				if ($thumb && $originalHeight < $thumb->getHeight() || !empty($options['height']) && $originalHeight < $options['height']) {
					$resizeWidth = ceil(($originalWidth * $resizeHeight) / $originalHeight);
				}
				$resizeDirname = $this->cropDirname . '/original-' . $media->getFilename();
				$resizeDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $resizeDirname);
				if(!$this->filesystem->exists($resizeDirname)) {
					$resizeThumb = imagecreatetruecolor($resizeWidth, $resizeHeight);
					$color = imagecolorallocatealpha($resizeThumb, 0, 0, 0, 127);
					imagecolortransparent($resizeThumb, $color);
					$source = ($this->createFunction)($originalDirname);
					imagecopyresampled($resizeThumb, $source, 0, 0, 0, 0, $resizeWidth, $resizeHeight, $originalWidth, $originalHeight);
					($this->imageFunction)($resizeThumb, $resizeDirname, $this->quality);
					$originalDirname = $resizeDirname;
				}
			}

			$newWidth = $thumb && is_numeric($thumb->getWidth()) ? $thumb->getWidth() : (!empty($options['width']) ? $options['width'] : $originalWidth);
			$newHeight = $thumb && is_numeric($thumb->getHeight()) > 0 ? $thumb->getHeight() : (!empty($options['height']) ? $options['height'] : $originalHeight);

			/** To set sizes for resize image before crop axis */
			if (empty($options['width']) && $originalWidth > $newWidth) {
				$resizeBeforeCropWidth = $newWidth;
				$ratio = $resizeBeforeCropWidth / $originalWidth;
				$resizeBeforeCropHeight = ceil($originalHeight * $ratio);
				if ($resizeBeforeCropHeight < $newHeight) {
					$resizeBeforeCropHeight = $newHeight;
					$ratio = $resizeBeforeCropHeight / $originalHeight;
					$resizeBeforeCropWidth = ceil($originalWidth * $ratio);
				}
//                if (($resizeBeforeCropWidth * 2) < $originalWidth) {
//                    $resizeBeforeCropWidth = ceil($resizeBeforeCropWidth * 2);
//                    $resizeBeforeCropHeight = ceil($resizeBeforeCropHeight * 2);
//                }
			}

			/** Resize image before crop axis */
			if (empty($options['width']) && !empty($resizeBeforeCropWidth) && !empty($resizeBeforeCropHeight)) {
				$thumbResizeDirname = str_replace('.' . $this->extension, '-thumb-resize-checker.' . $this->extension, $thumbDirname);
				if(!$this->filesystem->exists($thumbResizeDirname)) {
					$source = ($this->createFunction)($originalDirname);
					$imgResized = imagescale($source, $resizeBeforeCropWidth, $resizeBeforeCropHeight);
					($this->imageFunction)($imgResized, $thumbDirname, $this->quality);
					($this->imageFunction)($imgResized, $thumbResizeDirname, $this->quality);
				}
			}

			/** Generate crop thumbnail */
			$sourceDirname = empty($options['width']) && $this->filesystem->exists($thumbDirname) ? $thumbDirname : $originalDirname;
			$filename = str_replace('.' . $this->extension, '', $media->getFilename());
			if(!preg_match('/placeholder/', $thumbDirname)) {
				$thumbDirname = str_replace($filename, $filename . '-crop', $thumbDirname);
			}

			if ($this->filesystem->exists($sourceDirname)) {

				if(!$this->filesystem->exists($thumbDirname)) {

					$originalSource = ($this->createFunction)($sourceDirname);
					$cropAxis = $this->getAxis($originalSource, $newWidth, $newHeight);
					$dataX = $thumb && is_numeric($thumb->getDataX()) > 0 ? $thumb->getDataX() : $cropAxis['x'];
					$dataY = $thumb && is_numeric($thumb->getDataY()) > 0 ? $thumb->getDataY() : $cropAxis['y'];
					$thumbnail = imagecrop($originalSource, ['x' => $dataX, 'y' => $dataY, 'width' => $newWidth, 'height' => $newHeight]);
					$color = imagecolorallocatealpha($thumbnail, 0, 0, 0, 127);
					imagecolortransparent($thumbnail, $color);
					($this->imageFunction)($thumbnail, $thumbDirname, $this->quality);

					if ($thumb && $thumb->getId() && (is_numeric($thumb->getWidth()) || is_numeric($thumb->getHeight()) || is_numeric($thumb->getDataX()) || is_numeric($thumb->getDataY()))) {
						$this->forceSize = true;
					}
				}

				return $thumbDirname;
			}
		}

		return NULL;
	}

	/**
	 * Get thumbnail configuration dirname
	 *
	 * @param Media $media
	 * @param Thumb|null $thumb
	 * @param array $options
	 * @return string
	 */
	private function getThumbnailConfigurationDirname(Media $media, Thumb $thumb = NULL, array $options = []): string
	{
		$dirname = $this->cropDirname . '/' . $media->getScreen();

		if ($thumb) {
			$properties = ['width', 'height', 'dataX', 'dataY', 'scaleX', 'scaleY'];
			foreach ($properties as $property) {
				$getter = 'get' . ucfirst($property);
				if ($thumb->$getter()) {
					$dirname .= '-' . $thumb->$getter();
				}
			}
		} else {
			$properties = ['width', 'height'];
			foreach ($properties as $property) {
				if (!empty($options[$property])) {
					$dirname .= '-' . $property . '=' . $options[$property];
				}
			}
		}

		return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $dirname . '-' . $media->getFilename());
	}

	/**
	 * To generate thumbnail file
	 *
	 * @param Media $media
	 * @param int $size
	 * @param mixed $newWidth
	 * @param mixed $newHeight
	 * @param mixed $width
	 * @param mixed $height
	 * @param string $dirname
	 * @param string|null $thumb
	 * @return array
	 */
	private function thumbnailFile(Media $media, int $size, $newWidth, $newHeight, $width, $height, string $dirname, string $thumb = NULL): array
	{
		$filename = Urlizer::urlize($media->getFilename()) . '.' . $media->getExtension();
		$sizeFilename = $size . 'x' . $newHeight . '-' . $filename;

		if ($thumb) {
			list($width, $height) = getimagesize($thumb);
			$sizeFilename = $size . 'x' . $newHeight . '-' . $width . 'x' . $height . '-' . $filename;
			if ($this->forceSize) {
				$newWidth = $width;
				$newHeight = $height;
			}
			$matches = explode(DIRECTORY_SEPARATOR, $thumb);
			$filename = end($matches);
		}

		$this->fileDirname = Urlizer::urlize($filename);

		$thumbDirname = $this->generateDirectory($this->mediaDirname . '/' . Urlizer::urlize($this->fileDirname));
		$thumbDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $thumbDirname . '/' . $sizeFilename);
		$generateThumb = $width >= $newWidth;

		if ($generateThumb && !$this->filesystem->exists($thumbDirname)
			|| $this->forceSize && !$this->filesystem->exists($thumbDirname)
			|| preg_match('/cropper/', $this->request->getUri())) {
			$info = getimagesize($dirname);
			$source = ($this->createFunction)($dirname);
			$thumb = imagecreatetruecolor($newWidth, $newHeight);
			if ($info[2] == IMAGETYPE_GIF || $info[2] == IMAGETYPE_PNG) {
				$transparentInfo = imagecolortransparent($source);
                if ($transparentInfo >= 0) {
                    $transparentColors = imagecolorsforindex($source, $transparentInfo);
                    $transparentIndex = imagecolorallocate($thumb, $transparentColors['red'], $transparentColors['green'], $transparentColors['blue']);
                    imagefill($thumb, 0, 0, $transparentIndex);
                    imagecolortransparent($thumb, $transparentIndex);
                } elseif ($info[2] == IMAGETYPE_PNG) {
                    imagealphablending($thumb, false);
                    $color = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
                    imagefill($thumb, 0, 0, $color);
                    imagesavealpha($thumb, true);
                }
			}
			imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
			($this->imageFunction)($thumb, $thumbDirname, $this->quality);
		}

		return [
			'width' => $generateThumb ? $newWidth : $width,
			'height' => $generateThumb ? $newHeight : $height,
		];
	}

	/**
	 * To generate lazy file
	 *
	 * @param int $size
	 * @param array $sizes
	 * @return string
	 */
	private function lazyFile(int $size, array $sizes): string
	{
		$filename = $size . '-' . $sizes['width'] . 'x' . $sizes['height'] . '.png';
		$lazyDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $this->lazyDirname . '/' . $filename);

		if (!$this->filesystem->exists($lazyDirname)) {
			$img = imagecreatetruecolor($sizes['width'], $sizes['height']);
			$black = imagecolorallocate($img, 0, 0, 0);
			imagecolortransparent($img, $black);
			imagepng($img, $lazyDirname, 1);
		}

		return '/thumbnails/lazy/' . $filename;
	}

	/**
	 * Get thumbnails
	 *
	 * @param Media $media
	 * @return array
	 */
	private function getThumbnails(Media $media): array
	{
		$thumbnails = [];
		$dirname = $this->mediaDirname . DIRECTORY_SEPARATOR . Urlizer::urlize($this->fileDirname);
		$originalDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, 'uploads/' . $this->websiteDirectory . '/' . $media->getFilename());

		if ($this->filesystem->exists($dirname)) {

			$finder = new Finder();
			$finder->in($dirname);
			$mediaDirname = $media->getId() ?: 'placeholder';

			if ($this->filesystem->exists($this->mediaDirname) && in_array($media->getExtension(), self::ALLOWED_EXTENSIONS)) {
				foreach ($finder->files() as $file) {
					$matches = explode('x', $file->getFilename());
					if (is_numeric($matches[0])) {
						$thumbnails[$matches[0]] = 'thumbnails/' . $this->websiteDirectory . '/' . $mediaDirname . '/' . $this->fileDirname . '/' . $file->getFilename();
					}
				}
			}
		}

		foreach ($this->sizes as $size) {
			if (empty($thumbnails[$size])) {
				$thumbnails[$size] = $this->filesystem->exists($this->projectDirname . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $originalDirname)
					? $originalDirname : 'medias/placeholder.jpg';
			}
		}

		/** Set Medias by Screen */
		$screenSizes = ['mobile' => [320, 480], 'tablet' => [1024]];
		foreach ($media->getMediaScreens() as $mediaScreen) {
			$filename = $mediaScreen->getFilename();
			if (!empty($screenSizes[$mediaScreen->getScreen()]) && $filename) {
				foreach ($screenSizes[$mediaScreen->getScreen()] as $size) {
					$thumbnails[$size] = 'uploads/' . $this->websiteDirectory . '/' . $filename;
					$thumbnails[$size * 2] = 'uploads/' . $this->websiteDirectory . '/' . $filename;
				}
			}
		}

		ksort($thumbnails);

		return $this->setWebP($thumbnails);
	}

	/**
	 * Get axis coordinates for center crop
	 *
	 * @param $image
	 * @param $cropWidth
	 * @param $cropHeight
	 * @return array
	 */
	private function getAxis($image, $cropWidth, $cropHeight): array
	{
		return [
			'x' => $this->calculatePixelsForAlign(imagesx($image), $cropWidth, 'center')[0],
			'y' => $this->calculatePixelsForAlign(imagesy($image), $cropHeight, 'middle')[0]
		];
	}

	/**
	 * Calculate axis coordinates for crop
	 *
	 * @param $imageSize
	 * @param $cropSize
	 * @param $align
	 * @return array
	 */
	private function calculatePixelsForAlign($imageSize, $cropSize, $align): array
	{
		switch ($align) {
			case 'left':
			case 'top':
				return [0, min($cropSize, $imageSize)];
			case 'right':
			case 'bottom':
				return [max(0, $imageSize - $cropSize), min($cropSize, $imageSize)];
			case 'center':
			case 'middle':
				return [
					max(0, floor(($imageSize / 2) - ($cropSize / 2))),
					min($cropSize, $imageSize),
				];
			default:
				return [0, $imageSize];
		}
	}

	/**
	 * Generate webP image
	 *
	 * @param array $thumbnails
	 * @return array
	 */
	private function setWebP(array $thumbnails = []): array
	{
		$files = [];
		$webPSizes = $this->sizes;

		foreach ($thumbnails as $size => $thumbnail) {

			$matchesFilename = explode('/', $thumbnail);
			$filename = Urlizer::urlize(end($matchesFilename));
			$matchesExtension = explode('.', $thumbnail);
			$extension = end($matchesExtension);
			$path = $this->webpDirname . DIRECTORY_SEPARATOR . $filename . '.webp';
			$thumbnailPath = $this->projectDirname . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $thumbnail;
			$thumbnailPathExist = $this->filesystem->exists($thumbnailPath) && !is_dir($thumbnailPath);
			$createFunction = preg_match('/.png/', $thumbnail) ? 'imagecreatefrompng' : 'imagecreatefromjpeg';

			if ($thumbnailPathExist) {
				list($width, $height) = getimagesize($thumbnailPath);
				$thumbnailPathExist = $width > self::MIN_WIDTH;
			}

			if ($this->createFunction && !$this->filesystem->exists($path) && $thumbnailPathExist && in_array($size, $webPSizes) && in_array($extension, self::ALLOWED_WEBP_EXTENSIONS)) {
				$image = ($createFunction)($this->projectDirname . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $thumbnail);
				if ($createFunction === 'imagecreatefrompng') {
					imagepalettetotruecolor($image);
					imagealphablending($image, true);
					imagesavealpha($image, true);
				}
				imagewebp($image, $path, $this->quality);
			}

			$files[$size] = $this->filesystem->exists($path) ? str_replace([$this->projectDirname . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR], ['', '/'], $path) : $thumbnail;
		}

		return $files;
	}
}