<?php

namespace App\Twig\Content;

use App\Entity\Core\Website;
use App\Entity\Media\Media;
use App\Entity\Media\MediaRelation;
use App\Entity\Media\Thumb;
use App\Entity\Media\ThumbConfiguration;
use App\Entity\Translation\i18n;
use App\Service\Content\BrowserDetection;
use App\Service\Content\ImageThumbnail;
use App\Twig\Core\AppRuntime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Asset\Package;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * ThumbnailRuntime
 *
 * @property array UPLOADS_DIR
 * @property array ALLOWED_IMAGES_EXTENSIONS
 * @property int WIDTH_LIMIT_FULL
 * @property int WIDTH_LIMIT_CONTAINER
 *
 * @property Request $request
 * @property Environment $templating
 * @property FileRuntime $fileExtension
 * @property Package $assetsManager
 * @property MediaRuntime $mediaExtension
 * @property AppRuntime $appExtension
 * @property EntityManagerInterface $entityManager
 * @property BrowserDetection $browserDetection
 * @property KernelInterface $kernel
 * @property ImageThumbnail $imageThumbnail
 * @property array $options
 * @property array $arguments
 * @property array $runtimeConfig
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ThumbnailRuntime implements RuntimeExtensionInterface
{
	private const ENABLE_RATIO_GENERATOR = false;
	private const UPLOADS_DIR = 'uploads';
	private const ALLOWED_IMAGES_EXTENSIONS = ['png', 'jpg', 'jpeg'];
	private const WIDTH_LIMIT_FULL = 1903;
	private const WIDTH_LIMIT_CONTAINER = 1140;

	private $request;
	private $templating;
	private $fileExtension;
	private $assetsManager;
	private $mediaExtension;
	private $appExtension;
	private $entityManager;
	private $browserDetection;
	private $kernel;
	private $imageThumbnail;
	private $options = [];
	private $arguments = [];
	private $runtimeConfig = [];

	/**
	 * ThumbnailRuntime constructor.
	 *
	 * @param RequestStack $requestStack
	 * @param Environment $templating
	 * @param FileRuntime $fileExtension
	 * @param Package $assetsManager
	 * @param MediaRuntime $mediaExtension
	 * @param AppRuntime $appExtension
	 * @param EntityManagerInterface $entityManager
	 * @param BrowserDetection $browserDetection
	 * @param KernelInterface $kernel
	 * @param ImageThumbnail $imageThumbnail
	 */
	public function __construct(
		RequestStack $requestStack,
		Environment $templating,
		FileRuntime $fileExtension,
		Package $assetsManager,
		MediaRuntime $mediaExtension,
		AppRuntime $appExtension,
		EntityManagerInterface $entityManager,
		BrowserDetection $browserDetection,
		KernelInterface $kernel,
		ImageThumbnail $imageThumbnail)
	{
		$this->request = $requestStack->getMasterRequest();
		$this->templating = $templating;
		$this->fileExtension = $fileExtension;
		$this->assetsManager = $assetsManager;
		$this->mediaExtension = $mediaExtension;
		$this->appExtension = $appExtension;
		$this->entityManager = $entityManager;
		$this->browserDetection = $browserDetection;
		$this->kernel = $kernel;
		$this->imageThumbnail = $imageThumbnail;
	}

	/**
	 * Get file icon
	 *
	 * @param string $type
	 * @param string|NULL $extension
	 * @return string
	 */
	public function fileIcon(string $type, string $extension = NULL)
	{
		if (in_array($extension, self::ALLOWED_IMAGES_EXTENSIONS)) {
			return NULL;
		}

		$icons['admin'] = [
			'pdf' => 'fas fa-file-pdf',
			'docx' => 'fas fa-file-word',
			'xlsx' => 'fas fa-file-excel',
			'txt' => 'fas fa-file-alt',
			'mp4' => 'fas fa-video',
		];

		$icons['front'] = [
			'pdf' => 'fas fa-file-pdf',
			'docx' => 'fas fa-file-word',
			'xlsx' => 'fas fa-file-excel',
			'txt' => 'fas fa-file-alt',
			'mp4' => 'fas fa-video',
		];

		return !empty($icons[$type][$extension]) ? $icons[$type][$extension] : 'fas fa-file-alt';
	}

	/**
	 * Get Media Thumb
	 *
	 * @param null|MediaRelation|Media $entity
	 * @param null|ThumbConfiguration $thumbConfiguration
	 * @param bool $placeholder
	 * @return Thumb|null
	 */
	public function thumbConfiguration($entity, ThumbConfiguration $thumbConfiguration = NULL, bool $placeholder = false): ?Thumb
    {
		/** @var Media $media */
		$media = $entity;
		if ($media instanceof MediaRelation) {
			$media = $entity->getMedia();
		}

		if ($media && $thumbConfiguration) {
			foreach ($media->getThumbs() as $mediaThumb) {
				if ($mediaThumb->getConfiguration() === $thumbConfiguration) {
					return $mediaThumb;
				}
			}
		}

		$session = new Session();
		/** @var Website $website */
		$website = $session->get('frontWebsiteObject');
		$website = !$website ? $this->entityManager->getRepository(Website::class)->findOneByHost($this->request->getHost()) : $website;
		$logos = $this->mediaExtension->logos($website);

		$filename = NULL;
		if ($media instanceof Media && $media->getFilename()) {
			$filename = $media->getFilename();
		} elseif ($placeholder && is_array($logos) && !empty($logos['placeholder'])) {
			$filename = str_replace(['/uploads', '/' . $website->getUploadDirname() . '/'], ['', ''], $logos['placeholder']);
		}

		if ($filename) {

			$fileInfos = $this->fileExtension->fileInfo($website, $filename);
			$width = $thumbConfiguration ? $thumbConfiguration->getWidth() : $fileInfos->width;
			$height = $thumbConfiguration ? $thumbConfiguration->getHeight() : $fileInfos->height;
			$extension = $media ? $media->getExtension() : $fileInfos->getExtension();

			$thumb = new Thumb();
			$thumb->setWidth($width);
			$thumb->setHeight($height);

			$media = new Media();
			$media->setFilename($filename);
			$media->setWebsite($website);
			$media->setExtension($extension);

			if ($placeholder) {
				$media->setCategory('placeholder');
			}

			$thumb->setMedia($media);

			return $thumb;
		}

		return NULL;
	}

	/**
	 * Get thumbnail
	 *
	 * @param MediaRelation|Media|null $entity
	 * @param Thumb|null $thumb
	 * @param array $options
	 * @return string|array
	 * @throws LoaderError
	 * @throws RuntimeError
	 * @throws SyntaxError
	 */
	public function thumb($entity = NULL, Thumb $thumb = NULL, array $options = [])
	{
		$session = new Session();

		$this->arguments = [];
		$this->arguments['inPreview'] = preg_match('/\/preview\//', $this->request->getUri());
		$this->arguments['inAdmin'] = preg_match('/\/admin-' . $_ENV['SECURITY_TOKEN'] . '/', $this->request->getUri()) && !$this->arguments['inPreview'];
		$this->arguments['mediaRelation'] = $entity instanceof MediaRelation ? $entity : NULL;
		$this->arguments['popupGallery'] = !empty($options['popupGallery']) ? $options['popupGallery'] : NULL;
		$this->arguments['imgClass'] = !empty($options['class']) ? $options['class'] : NULL;
		$this->arguments['parentEntity'] = !empty($options['parentEntity']) ? $options['parentEntity'] : NULL;
		$this->arguments['targetLink'] = !empty($options['targetLink']) ? $options['targetLink'] : NULL;
		$this->arguments['lazyLoad'] = isset($options['lazyLoad']) ? $options['lazyLoad'] : true;
		$this->arguments['fullPopup'] = isset($options['fullPopup']) ? $options['fullPopup'] : true;
		$this->arguments['displayPage'] = isset($options['displayPage']) ? $options['displayPage'] : true;
		$this->arguments['targetPageI18n'] = isset($options['displayPage']) ? $options['targetPageI18n'] : NULL;
		$this->arguments['titleForce'] = isset($options['titleForce']) ? $options['titleForce'] : 3;

		$media = $entity;
		if ($media instanceof MediaRelation) {
			/** @var Media $media */
			$media = $entity->getMedia();
			$this->arguments['mediaRelation'] = $entity;
			$this->arguments['displayTitle'] = $entity->getDisplayTitle();
			$this->arguments['popup'] = $entity->getPopup();
			$this->arguments['downloadable'] = $entity->getDownloadable();
		}

		$media = !$media && $thumb ? $thumb->getMedia() : $media;
		$this->arguments['isPlaceholder'] = $media instanceof Media && $media->getCategory() === 'placeholder';
		$this->arguments['notContractual'] = $media instanceof Media ? $media->getNotContractual() : false;

		if (!$media && isset($options['placeholder'])
			|| $media instanceof Media && !$media->getFilename() && isset($options['placeholder'])) {
			$media = new Media();
			$media->setExtension('jpeg');
			$media->setFilename($this->assetsManager->getUrl('build/vendor/images/placeholder.jpg'));
			$media->setWebsite($session->get('frontWebsiteObject'));
			$this->arguments['isPlaceholder'] = true;
		}

		if (!$media) {
			return false;
		}

		$this->options = $options;
		$filename = $media->getFilename();

		if (!$filename) {
			return false;
		}

		$website = $media->getWebsite();
		$websiteDirname = $website instanceof Website ? $website->getUploadDirname() : NULL;

		if ($websiteDirname) {
			$this->getMediasScreens($website, $media, $websiteDirname);
			$this->getDesktop($website, $media, $websiteDirname, $filename, $thumb);
		}

		$this->arguments['path'] = $this->arguments['desktop']['path'];

		if (!empty($this->options['path'])) {

			$path = $this->arguments['path'];

			if ($this->options['path'] === 'desktop' && !empty($this->arguments['desktop']['path'])) {
				$path = $this->arguments['desktop']['path'];
			}
			if (is_bool($this->options['path']) && $this->browserDetection->isTablet() && !empty($this->arguments['tablet']['path'])
				|| $this->options['path'] === 'tablet' && !empty($this->arguments['tablet']['path'])) {
				$path = $this->arguments['tablet']['path'];
			} elseif (is_bool($this->options['path']) && $this->browserDetection->isMobile() && !empty($this->arguments['mobile']['path'])
				|| $this->options['path'] === 'mobile' && !empty($this->arguments['mobile']['path'])
				|| $this->options['path'] === 'tablet' && empty($this->arguments['tablet']['path']) && !empty($this->arguments['mobile']['path'])) {
				$path = $this->arguments['mobile']['path'];
			}

			return str_replace('//', '/', '/' . $path);
		}

		$extension = $this->arguments['extension'] = $this->arguments['desktop']['media']->getExtension();
		$this->arguments['btnLink'] = isset($this->options['btn_link']) && $this->options['btn_link'];

		$this->inBox($website, $extension);

		$exceptionExceptions = ['svg', 'gif', 'tiff'];
		$isImage = property_exists($this->arguments['desktop']['infos'], 'isImage') && $this->arguments['desktop']['infos']->isImage;

		if ($isImage) {
			$this->arguments['thumbnails'] = $this->imageThumbnail->execute($media, $thumb, $options);
		}

		if (!empty($this->options['config_path'])) {
			return $this->arguments;
		} elseif ($isImage || in_array($extension, $exceptionExceptions)) {
			echo $this->templating->render('core/image-config.html.twig', $this->arguments);
		} elseif (!$this->arguments['inAdmin'] && $extension === 'mp4') {
			echo $this->templating->render('core/video.html.twig', $this->arguments);
		} elseif (!$this->arguments['inAdmin'] || $this->arguments['inPreview']) {
			$this->arguments['website'] = $this->getFrontWebsite($session);
			$this->arguments['websiteTemplate'] = $this->arguments['website']->getConfiguration()->getTemplate();
			echo $this->templating->render('front/' . $this->arguments['websiteTemplate'] . '/blocks/file/default.html.twig', $this->arguments);
		}
	}

	/**
	 * To generate image render with options
	 *
	 * @param array $options
	 *
	 * @return string|null
	 * @throws LoaderError
	 * @throws RuntimeError
	 * @throws SyntaxError
	 */
	public function imgRender(array $options = []): ?string
	{
		$isImageThumb = !preg_match('/.svg/', $options['src'])
			&& !preg_match('/.gif/', $options['src'])
			&& !preg_match('/.tiff/', $options['src']);

		$this->runtimeConfig = [];
		if (!isset($options['width']) && isset($options['height'])) {
			if (!isset($options['thumb']) && $isImageThumb || isset($options['thumb']) && $options['thumb'] !== false && $isImageThumb) {
				$this->runtimeConfig['upscale']['min'] = [$options['width'], $options['height']];
				$this->runtimeConfig['thumbnail']['size'] = [$options['width'], $options['height']];
				$this->runtimeConfig['thumbnail']['mode'] = "outbound";
			}
		}

		$title = !empty($options['title']) ? $options['title'] : NULL;
		if (!$title) {
			$matches = explode('/', $options['src']);
			$title = end($matches);
		}

		$filesystem = new Filesystem();
		$htmlDirname = preg_match('/include\/svg/', $options['src']) ? $this->kernel->getProjectDir() . '/templates/' . ltrim($options['src'], '/')
			: $this->kernel->getProjectDir() . '/public' . $options['src'];
		$htmlDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $htmlDirname);

		if (!empty($options['only_html']) && $options['only_html'] && preg_match('/.svg/', $options['src']) && $filesystem->exists($htmlDirname)) {

			$svg = file_get_contents($htmlDirname);

			/** Replace id if option not exist */
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

			/** Replace classes */
			preg_match_all('/class="([^"]*)"/', $svg, $matches);
			if (!empty($matches[1])) {
				$alreadySet = [];
				foreach ($matches[1] as $svgMatches) {
					if (!in_array($svgMatches, $alreadySet)) {
						$svg = str_replace($svgMatches, $this->appExtension->charsId(), $svg);
						$alreadySet[] = $svgMatches;
					}
				}
			}

			/** Set attributes svg by options */
			$attributes = ['id', 'width', 'height', 'fill', 'class'];
			$svg = str_replace('\'', '"', $svg);
			preg_match("/<svg[^>]*/i", $svg, $matches);
			if (!empty($matches[0])) {
				$replaceInSvg = $originalSvg = $matches[0];
				foreach ($attributes as $attribute) {
					if (isset($options[$attribute])) {
						$replaceInSvg = preg_replace('!\\s+' . $attribute . '=("|\')?[-_():;a-z0-9 ]+("|\')?!i', '', strval($replaceInSvg));
						$replaceInSvg = preg_replace('/(<svg\b[^><]*)/i', '$1 ' . $attribute . '="' . $options[$attribute] . '"', $replaceInSvg);
					}
				}
				$svg = str_replace($originalSvg, $replaceInSvg, $svg);
			}

			echo preg_replace('/<!(?!<!)[^\[>].*?>/', '', $svg);

		} else {

			echo $this->templating->render('core/lazy-thumb.html.twig', [
				'src' => $options['src'],
				'runtimeConfig' => $this->runtimeConfig,
				'id' => !empty($options['id']) ? $options['id'] : NULL,
				'class' => !empty($options['class']) ? $options['class'] : NULL,
				'devMode' => !empty($options['dev']) ? $options['dev'] : NULL,
				'lazy' => !isset($options['lazy']) ? true : $options['lazy'],
				'width' => !empty($options['width']) ? $options['width'] : NULL,
				'height' => !empty($options['height']) ? $options['height'] : NULL,
				'title' => $title,
				'quality' => !empty($options['quality']) ? $options['quality'] : 72
			]);
		}

		return NULL;
	}

	/**
	 * Get Medias screens
	 *
	 * @param Website $website
	 * @param Media $media
	 * @param string $websiteDirname
	 */
	private function getMediasScreens(Website $website, Media $media, string $websiteDirname)
	{
		foreach ($media->getMediaScreens() as $mediaScreen) {
			$filename = $mediaScreen->getFilename();
			if ($filename) {
				$fileInfos = $this->fileExtension->fileInfo($website, $filename);
				$this->arguments[$mediaScreen->getScreen()] = [
					'path' => property_exists($fileInfos, 'isPlaceHolder') && $fileInfos->isPlaceHolder
						? $fileInfos->dir : self::UPLOADS_DIR . '/' . $websiteDirname . '/' . $filename,
					'infos' => $fileInfos,
					'attr' => $this->setAttributes($mediaScreen, $fileInfos)
				];
			}
		}
	}

	/**
	 * Get desktop Media
	 *
	 * @param Website $website
	 * @param Media $media
	 * @param string $websiteDirname
	 * @param string $filename
	 * @param Thumb|null $thumb
	 * @return mixed
	 */
	private function getDesktop(Website $website, Media $media, string $websiteDirname, string $filename, Thumb $thumb = NULL)
	{
		$fileInfos = $this->fileExtension->fileInfo($website, $filename);

		$this->arguments['desktop']['infos'] = $fileInfos;
		$this->arguments['desktop']['attr'] = $this->setAttributes($media, $fileInfos, $thumb);
		$this->arguments['desktop']['media'] = $media;
		$this->arguments['desktop']['thumb'] = $thumb;
		$this->arguments['desktop']['path'] = $fileInfos && property_exists($fileInfos, 'isPlaceHolder') && $fileInfos->isPlaceHolder
			? $fileInfos->dir : self::UPLOADS_DIR . '/' . $websiteDirname . '/' . $filename;
		$this->filters($media, $fileInfos, $thumb);

		return $fileInfos;
	}

	/**
	 * Set attributes
	 *
	 * @param Media $media
	 * @param SplFileInfo|null $fileInfos
	 * @param Thumb|NULL $thumb
	 * @return array
	 */
	private function setAttributes(Media $media, SplFileInfo $fileInfos = NULL, Thumb $thumb = NULL): array
	{
		$title = $fileInfos ? $fileInfos->getFilename() : '';
		foreach ($media->getI18ns() as $i18n) {
			$localeTitle = $i18n->getTitle();
			if ($i18n->getLocale() === $this->request->getLocale() && $localeTitle) {
				$title = $localeTitle;
				break;
			}
		}

		$width = $height = NULL;
		if ($thumb || $fileInfos) {
			$width = $thumb ? $thumb->getWidth() : $fileInfos->width;
			$height = $thumb ? $thumb->getHeight() : $fileInfos->height;
		}

		$width = !empty($this->options['width']) ? $this->options['width'] : $width;
		$height = !empty($this->options['height']) ? $this->options['height'] : $height;

		if (!empty($this->arguments['desktop']['attr']['width'])) {
			$width = $this->arguments['desktop']['attr']['width'];
		}

		if (!empty($this->arguments['desktop']['attr']['height'])) {
			$height = $this->arguments['desktop']['attr']['height'];
		}

		if ($this->arguments['mediaRelation'] && !empty($this->arguments['mediaRelation']->getMaxWidth())) {
			$width = $this->arguments['mediaRelation']->getMaxWidth();
		}

		if ($this->arguments['mediaRelation'] && !empty($this->arguments['mediaRelation']->getMaxHeight())) {
			$height = $this->arguments['mediaRelation']->getMaxHeight();
		}

		return [
			'width' => !$this->arguments['inAdmin'] ? $width : NULL,
			'height' => !$this->arguments['inAdmin'] ? $height : NULL,
			'title' => $title,
			'author' => $media->getCopyright()
		];
	}

	/**
	 * Generate filters
	 *
	 * @param Media $media
	 * @param SplFileInfo $fileInfos
	 * @param Thumb|NULL $thumb
	 */
	private function filters(Media $media, SplFileInfo $fileInfos, Thumb $thumb = NULL)
	{
		$this->runtimeConfig = [];

		if (in_array($media->getExtension(), self::ALLOWED_IMAGES_EXTENSIONS)) {
			$this->setRotate($thumb);
			$this->setCropFilter($thumb);
			$this->setThumbnail($fileInfos, $thumb);
		}

		$this->arguments['desktop']['runtimeConfig'] = $this->runtimeConfig;
	}

	/**
	 * Set rotate
	 *
	 * @param Thumb|NULL $thumb
	 */
	private function setRotate(Thumb $thumb = NULL)
	{
		if ($thumb && $thumb->getRotate()) {
			$this->runtimeConfig['rotate']['angle'] = $thumb->getRotate();
		}
	}

	/**
	 * Set crop filter
	 *
	 * @param Thumb|NULL $thumb
	 */
	private function setCropFilter(Thumb $thumb = NULL)
	{
		$sizeForce = !empty($this->options['maxHeight']) || !empty($this->options['maxWidth']);

		if (!$sizeForce && $thumb && is_int($thumb->getDataX()) && is_int($thumb->getDataY())) {
			$this->runtimeConfig['crop']['size'] = [$thumb->getWidth(), $thumb->getHeight()];
			$this->runtimeConfig['crop']['start'] = [$thumb->getDataX(), $thumb->getDataY()];
		}

		if ($thumb && $thumb->getScaleX() == -1) {
			$this->runtimeConfig['flip']['axis'] = 'x';
		}
	}

	/**
	 * Set thumbnail
	 *
	 * @param SplFileInfo $fileInfos
	 * @param Thumb|NULL $thumb
	 * @return array
	 */
	private function setThumbnail(SplFileInfo $fileInfos, Thumb $thumb = NULL)
	{
		$width = NULL;
		$height = NULL;
		$this->arguments['desktop']['forceSize'] = false;

		/** @var MediaRelation $mediaRelation */
		$mediaRelation = !empty($this->arguments['mediaRelation']) ? $this->arguments['mediaRelation'] : NULL;
		$hasCache = !isset($this->options['cache']) || isset($this->options['cache']) && $this->options['cache'] != false;
		$sizeForce = !empty($this->options['maxHeight']) || !empty($this->options['maxWidth']);

		if (!$sizeForce && $thumb && $thumb->getHeight() && $thumb->getWidth() || !$sizeForce && !empty($this->options['width']) && !empty($this->options['height'])) {
			$width = $thumb && $thumb->getWidth() ? $thumb->getWidth() : $this->options['width'];
			$height = $thumb && $thumb->getHeight() ? $thumb->getHeight() : $this->options['height'];
		}

		$this->arguments['maxWidthClass'] = $mediaRelation && $mediaRelation->getMaxWidth() ? 'max-width' : '';

		if (!$this->arguments['inAdmin'] && $mediaRelation && $mediaRelation->getMaxWidth() && $mediaRelation->getMaxHeight()) {
			$width = $mediaRelation->getMaxWidth();
			$height = $mediaRelation->getMaxHeight();
		}

		$this->arguments['desktop']['forceSize'] = !$this->arguments['inAdmin'] && $mediaRelation && ($mediaRelation->getMaxWidth() || $mediaRelation->getMaxHeight());

		if (!$this->arguments['inAdmin'] && !empty($this->options['maxHeight']) && !empty($this->options['maxWidth'])) {
			$hasCache = true;
			$this->arguments['desktop']['attr']['maxWidth'] = $width = $this->options['maxWidth'];
			$this->arguments['desktop']['attr']['height'] = $height = $this->options['maxHeight'];
		}

		$widthLimit = isset($this->options['zoneFullSize']) && !$this->options['zoneFullSize'] ? self::WIDTH_LIMIT_CONTAINER : self::WIDTH_LIMIT_FULL;
		$width = !$width ? $fileInfos->width : $width;
		$height = !$height ? $fileInfos->height : $height;

		if ($width > $widthLimit) {
			$originalWidth = !$sizeForce && $thumb && $thumb->getWidth() > 0 ? $thumb->getWidth() : $width;
			$originalHeight = !$sizeForce && $thumb && $thumb->getWidth() > 0 ? $thumb->getHeight() : $height;
			$width = $widthLimit;
			$height = ceil($originalHeight / ($originalWidth / $width));
		}

		if (self::ENABLE_RATIO_GENERATOR) {
			$colSizeRatio = !empty($this->options['colSize']) ? 12 / $this->options['colSize'] : 0;
			$maxWidth = $colSizeRatio > 0 ? ceil($widthLimit / $colSizeRatio) : 0;
			if ($maxWidth > 0 && $width > $maxWidth) {
				$originalWidth = $width;
				$width = $maxWidth;
				$height = ceil($height / ($originalWidth / $width));
			}
		}

		if (!$this->arguments['inAdmin'] && $mediaRelation && $mediaRelation->getMaxWidth() > 0 && !$mediaRelation->getMaxHeight()) {

			$this->arguments['desktop']['attr']['width'] = $mediaRelation->getMaxWidth();
			$this->arguments['desktop']['attr']['height'] = 'auto';

			$this->runtimeConfig['relative_resize']['widen'] = $mediaRelation->getMaxWidth();
		} elseif (!$this->arguments['inAdmin'] && $mediaRelation && $mediaRelation->getMaxHeight() > 0 && !$mediaRelation->getMaxWidth()) {

			$this->arguments['desktop']['attr']['width'] = 'auto';
			$this->arguments['desktop']['attr']['height'] = $mediaRelation->getMaxHeight();

			$this->runtimeConfig['relative_resize']['heighten'] = $mediaRelation->getMaxHeight();
		} else {

			$this->arguments['desktop']['attr']['width'] = $width;
			$this->arguments['desktop']['attr']['height'] = $height;

			if ($hasCache && $width && $height) {
				$this->runtimeConfig['upscale']['min'] = [$width, $height];
				$this->runtimeConfig['thumbnail']['size'] = [$width, $height];
				$this->runtimeConfig['thumbnail']['mode'] = "outbound";
			}
		}

		return $this->runtimeConfig;
	}

	/**
	 * Check if MediaRelation is in box
	 *
	 * @param Website $website
	 * @param string|null $extension
	 * @return bool
	 */
	private function inBox(Website $website, string $extension = NULL)
	{
		if (isset($this->options['isInBox'])) {
			return $this->arguments['inBox'] = $this->options['isInBox'];
		}

		$infos = $this->arguments['desktop']['infos'];
		$exceptionExceptions = ['svg', 'gif', 'tiff'];
		$allowed = $this->arguments['mediaRelation']
			&& !$this->arguments['inAdmin']
			&& !isset($this->options['notInBox'])
			&& $website->getConfiguration()->getHoverTheme()
			&& (property_exists($infos, 'isImage') && $infos->isImage || in_array($extension, $exceptionExceptions));

		/** @var MediaRelation $mediaRelation */
		$mediaRelation = $this->arguments['mediaRelation'];
		$mediaRelationI18n = $mediaRelation instanceof MediaRelation ? $mediaRelation->getI18n() : NULL;

		if ($allowed && $mediaRelation instanceof MediaRelation && ($this->arguments['mediaRelation']->getDownloadable()
				|| $this->arguments['mediaRelation']->getPopup())
			|| $allowed && $mediaRelationI18n instanceof i18n && ($mediaRelationI18n->getTargetLink()
				|| $mediaRelationI18n->getTargetPage())) {
			return $this->arguments['inBox'] = true;
		}

		return $this->arguments['inBox'] = false;
	}

	/**
	 * Get Front Website
	 *
	 * @param Session $session
	 * @return Website
	 */
	private function getFrontWebsite(Session $session)
	{
		$website = $session->get('frontWebsite');

		if (!$website instanceof Website) {
			$website = $this->entityManager->getRepository(Website::class)->findOneByHost($this->request->getHost());
		}

		return $website;
	}
}