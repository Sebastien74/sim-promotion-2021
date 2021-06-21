<?php

namespace App\Form\Manager\Media;

use App\Entity\Core\Configuration;
use App\Entity\Core\Website;
use App\Entity\Layout\Block;
use App\Entity\Layout\Layout;
use App\Entity\Layout\Zone;
use App\Entity\Media\Media;
use App\Entity\Media\MediaRelation;
use App\Entity\Media\Thumb;
use App\Entity\Seo\Seo;
use App\Entity\Translation\i18n;
use App\Helper\Core\InterfaceHelper;
use App\Service\Content\ImageThumbnail;
use App\Service\Core\Uploader;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * MediaManager
 *
 * Manage admin Media form
 *
 * @property array SCREENS
 * @property array ALLOWED_IMAGES_EXTENSIONS
 *
 * @property Uploader $uploader
 * @property EntityManagerInterface $entityManager
 * @property Request $request
 * @property InterfaceHelper $interfaceHelper
 * @property TranslatorInterface $translator
 * @property string $interfaceName
 * @property array $localesSet
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class MediaManager
{
    private const SCREENS = ['tablet', 'mobile'];
    private const ALLOWED_IMAGES_EXTENSIONS = ['png', 'jpg', 'jpeg'];

    private $uploader;
    private $entityManager;
    private $request;
    private $interfaceHelper;
    private $translator;
    private $interfaceName;
    private $localesSet = [];

    /**
     * MediaManager constructor.
     *
     * @param Uploader $uploader
     * @param EntityManagerInterface $entityManager
     * @param RequestStack $requestStack
     * @param InterfaceHelper $interfaceHelper
     * @param TranslatorInterface $translator
     */
    public function __construct(
        Uploader $uploader,
        EntityManagerInterface $entityManager,
        RequestStack $requestStack,
        InterfaceHelper $interfaceHelper,
        TranslatorInterface $translator)
    {
        $this->uploader = $uploader;
        $this->entityManager = $entityManager;
        $this->request = $requestStack->getMasterRequest();
        $this->interfaceHelper = $interfaceHelper;
        $this->translator = $translator;
    }

    /**
     * Synchronize locales MediaRelations if no exist
     *
     * @param Website $website
     * @param array $interface
     * @param $entity
     * @param MediaRelation $mediaRelation
     */
    public function synchronizeLocales(Website $website, array $interface, $entity, MediaRelation $mediaRelation)
    {
        $this->localesSet = [];
        foreach ($website->getConfiguration()->getAllLocales() as $locale) {
            $this->setEntity($interface, $locale, $entity, $mediaRelation);
        }
    }

    /**
     * Synchronize locale Media screens
     *
     * @param Media $media
     */
    public function synchronizeScreens(Media $media)
    {
        if (in_array($media->getExtension(), self::ALLOWED_IMAGES_EXTENSIONS)) {

            foreach (self::SCREENS as $screen) {

                $exist = $this->screenExist($media, $screen);

                if (!$exist) {

                    $mediaScreen = new Media();
                    $mediaScreen->setMedia($media);
                    $mediaScreen->setScreen($screen);
                    $mediaScreen->setWebsite($media->getWebsite());
                    $media->addMediaScreen($mediaScreen);

                    $this->entityManager->persist($media);
                    $this->entityManager->flush();
                    $this->entityManager->refresh($media);
                }
            }
        }
    }

    /**
     * Get default locale Media
     * Get default locale Media
     *
     * @param Media $media
     * @param string $screen
     * @return bool
     */
    private function screenExist(Media $media, string $screen): bool
    {
        foreach ($media->getMediaScreens() as $mediaScreen) {
            if ($mediaScreen->getScreen() === $screen) {
                return true;
            }
        }

        return false;
    }

    /**
     * Post Media
     *
     * @param Form $form
     * @param Website $website
     * @param array $interface
     * @throws NonUniqueResultException
     */
    public function post(Form $form, Website $website, array $interface = [])
    {
        $entity = $form->getData();
        $this->interfaceName = !empty($interface['name']) ? $interface['name'] : NULL;

        if (method_exists($entity, 'setUpdatedAt')) {
            $entity->setUpdatedAt(new DateTime('now'));
            $this->entityManager->persist($entity);
        }

        /** Media screen */
        if ($entity instanceof Media) {
            foreach ($form['mediaScreens'] as $mediaScreenForm) {
                $uploadedFile = $mediaScreenForm['uploadedFile']->getData();
                $media = $mediaScreenForm->getData();
                if ($uploadedFile) {
                    $this->setUploadedMedia($uploadedFile, $media, $website);
                }
            }
        }

        /** Media video */
        if ($entity instanceof Block && $form->getName() === 'video') {
            foreach ($form['mediaRelations'] as $mediaRelation) {
                foreach ($mediaRelation['media']['mediaScreens'] as $screen) {
                    $uploadedFile = $screen['uploadedFile']->getData();
                    if ($uploadedFile) {
                        $this->setUploadedMedia($uploadedFile, $screen->getData(), $website);
                    }
                }
            }
        }

        /** Multiple uploaded files */
        if (!empty($form['medias'])) {
            foreach ($form['medias'] as $uploadedFilesForm) {
                $uploadedFiles = $uploadedFilesForm->getData();
                foreach ($uploadedFiles as $uploadedFile) {
                    $this->multiUploadedFiles($uploadedFile, $website, $entity);
                }
            }
        } /** Update Media (in media library) */
        elseif (!empty($form['uploadedFile'])) {
            $uploadedFile = $form['uploadedFile']->getData();
            if ($uploadedFile) {
                $this->setUploadedMedia($uploadedFile, $entity, $website);
            }
        } /** Single uploaded file mediaRelations Collection */
        elseif (method_exists($entity, 'getMediaRelations') && !empty($form['mediaRelations'])) {
            $this->singleUploadedFile($form, $website, $entity);
        } /** Single uploaded file mediaRelation */
        elseif (method_exists($entity, 'getMediaRelation') && !empty($form['mediaRelation'])) {
            $this->singleUploadedLocaleFile($form, $website, $entity);
        } /** Single uploaded file MediaRelation entity */
        elseif ($entity instanceof MediaRelation) {
            $uploadedFile = $this->request->files->get('media_relation') ? $this->request->files->get('media_relation')['media']['uploadedFile'] : NULL;
            if (!$uploadedFile && !empty($this->request->files->get('media_relation_' . $entity->getId())['media']['uploadedFile'])) {
                $uploadedFile = $this->request->files->get('media_relation_' . $entity->getId())['media']['uploadedFile'];
            }
            if ($uploadedFile) {
                $this->setUploadedMediaMediaRelation($uploadedFile, $entity, $entity->getMedia(), $website);
            }
        }

        if ($entity instanceof Media) {
            $this->setMedia($entity);
        }

        if ($entity instanceof MediaRelation) {

            $i18n = $entity->getI18n();
            if ($i18n && !$i18n->getWebsite()) {
                $i18n->setWebsite($website);
            }

            $media = $entity->getMedia();
            if (!$media->getWebsite()) {
                $media->setWebsite($website);
            }
        }
    }

    /**
     * Multi uploaded files
     *
     * @param UploadedFile $uploadedFile
     * @param Website $website
     * @param mixed $entity
     * @throws NonUniqueResultException
     */
    private function multiUploadedFiles(UploadedFile $uploadedFile, Website $website, $entity)
    {
        $configuration = $website->getConfiguration();

        $isUpload = $this->uploader->upload($uploadedFile, $website);

        if ($isUpload) {

            $media = new Media();
            $media->setFilename($this->uploader->getFilename());
            $media->setName($this->uploader->getName());
            $media->setExtension($this->uploader->getExtension());
            $media->setWebsite($website);

            $this->entityManager->persist($media);

            if (!$entity instanceof Website && property_exists($entity, 'mediaRelations')) {

                $classname = $this->entityManager->getClassMetadata(get_class($entity))->getName();
                $repository = $this->entityManager->getRepository($classname);
                $queryForPosition = $repository->createQueryBuilder('e')->select('e')
                    ->leftJoin('e.mediaRelations', 'm')
                    ->andWhere('m.locale = :locale')
                    ->andWhere('e.id = :id')
                    ->setParameter('locale', $configuration->getLocale())
                    ->setParameter('id', $entity->getId())
                    ->addSelect('m')
                    ->getQuery()
                    ->getOneOrNullResult();
                $position = $queryForPosition ? $queryForPosition->getMediaRelations()->count() + 1 : 1;

                $mediaRelation = new MediaRelation();
                $mediaRelation->setLocale($configuration->getLocale());
                $mediaRelation->setMedia($media);
                $mediaRelation->setPosition($position);
                $mediaRelation->setCategory($this->interfaceName);

                $entity->addMediaRelation($mediaRelation);

                $this->setI18nMedia($mediaRelation, $media);

                $this->entityManager->persist($mediaRelation);

                $this->initializeLocales($configuration, $entity, $website);
            }
        }
    }

    /**
     * Single uploaded file
     *
     * @param Form $form
     * @param Website $website
     * @param $entity
     */
    private function singleUploadedFile(Form $form, Website $website, $entity)
    {
        $configuration = $website->getConfiguration();

        foreach ($form['mediaRelations'] as $relation) {

            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $relation['media']['uploadedFile']->getData();
            /** @var MediaRelation $mediaRelation */
            $mediaRelation = $relation->getData();
            /** @var Media $media */
            $media = $relation['media']->getData();

            if (!$media->getWebsite()) {
                $media->setWebsite($website);
            }

            /** File upload */
            if ($uploadedFile) {
                $this->setUploadedMediaMediaRelation($uploadedFile, $mediaRelation, $media, $website);
            }
        }

        if ($form->getName() !== 'video') {
            $this->initializeLocales($configuration, $entity, $website);
        }
    }

    /**
     * Set uploaded Media
     *
     * @param UploadedFile $uploadedFile
     * @param MediaRelation $mediaRelation
     * @param Media $media
     * @param Website $website
     */
    private function setUploadedMediaMediaRelation(UploadedFile $uploadedFile, MediaRelation $mediaRelation, Media $media, Website $website)
    {
        $isUpload = $this->uploader->upload($uploadedFile, $website);

        if ($isUpload) {

            /** Change media on updated (Except in Media library) */
            if (!empty($this->uploader->getFilename()) && $media->getFilename() !== $this->uploader->getFilename()) {

                $oldMedia = $media;

                $media = new Media();
                $media->setWebsite($website);
                $media->setCategory($oldMedia->getCategory());
                $media->setFolder($oldMedia->getFolder());
                $media->setScreen($oldMedia->getScreen());
                $mediaRelation->setMedia($media);
                $this->entityManager->persist($oldMedia);

                $this->setI18nMedia($mediaRelation, $media);

                foreach ($oldMedia->getMediaScreens() as $screen) {
                    $screen->setMedia($media);
                    $this->entityManager->persist($screen);
                }
            }

            $media->setFilename($this->uploader->getFilename());
            $media->setName($this->uploader->getName());
            $media->setExtension($this->uploader->getExtension());

            /** Remove Media if filename is empty */
            if (!$media->getFilename()) {
                $mediaRelation->setMedia(NULL);
                $this->entityManager->remove($media);
            }
        }
    }

    /**
     * Single Uploaded locale file
     *
     * @param Form $form
     * @param Website $website
     * @param $entity
     */
    private function singleUploadedLocaleFile(Form $form, Website $website, $entity)
    {
        /** @var MediaRelation $mediaRelation */
        $mediaRelation = $form['mediaRelation']->getData();
        $media = $mediaRelation->getMedia();
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $form['mediaRelation']['media']['uploadedFile']->getData();
        $locale = property_exists($entity, 'locale')
            ? $entity->getLocale() : $this->request->get('entitylocale');

        if (!$mediaRelation->getLocale()) {
            $mediaRelation->setLocale($locale);
        }

        if (!$media->getWebsite()) {
            $media->setWebsite($website);
        }

        if ($uploadedFile) {

            $isUpload = $this->uploader->upload($uploadedFile, $website);

            if ($isUpload) {

                /** Change media on updated */
                if (!empty($media->getFilename()) && $media->getFilename() !== $this->uploader->getFilename()) {

                    $oldMedia = $media;

                    $media = new Media();
                    $media->setWebsite($website);
                    $media->setCategory($oldMedia->getCategory());
                    $media->setFolder($oldMedia->getFolder());
                    $mediaRelation->setMedia($media);
                }

                $media->setExtension($this->uploader->getExtension());
                $media->setFilename($this->uploader->getFilename());
                $media->setName($this->uploader->getName());
            }
        }
    }

    /**
     * Update Media
     *
     * @param UploadedFile $uploadedFile
     * @param Media $media
     * @param Website $website
     */
    private function setUploadedMedia(UploadedFile $uploadedFile, Media $media, Website $website)
    {
        if ($media->getFilename()) {
            $this->uploader->removeFile($media->getFilename());
        }

        $isUpload = $this->uploader->upload($uploadedFile, $website);

        if ($isUpload) {
            $media->setFilename($this->uploader->getFilename());
            $media->setName($this->uploader->getName());
            $media->setExtension($this->uploader->getExtension());
        }
    }

    /**
     * Initialize locales MediaRelation & Media Website
     *
     * @param Configuration $configuration
     * @param $entity
     * @param Website $website
     */
    private function initializeLocales(Configuration $configuration, $entity, Website $website)
    {
        $defaultLocaleMedia = NULL;
        foreach ($entity->getMediaRelations() as $mediaRelation) {

            /** Get default locale Media */
            if ($mediaRelation->getLocale() === $configuration->getLocale()) {
                $defaultLocaleMedia = $mediaRelation->getMedia();
            }

            /** Set Media Website */
            $media = !$mediaRelation->getMedia() ? new Media() : $mediaRelation->getMedia();
            if (!$media->getWebsite()) {
                $media->setWebsite($website);
            }

            /** @var i18n $i18n */
            $i18n = $mediaRelation->getI18n();
            if ($i18n && !$i18n->getLocale()) {
                $i18n->setLocale($mediaRelation->getLocale());
                $i18n->setWebsite($website);
            }
        }

        /** Set others locales Medias if is new and Media empty */
        foreach ($entity->getMediaRelations() as $mediaRelation) {
            if (!$mediaRelation->getIsInit() && $mediaRelation->getLocale() !== $configuration->getLocale() && $defaultLocaleMedia && !$mediaRelation->getMedia()->getFilename()) {
                $media = $mediaRelation->getMedia();
                if ($media->getId()) {
                    $this->entityManager->remove($media);
                }
                $mediaRelation->setMedia($defaultLocaleMedia);
                $mediaRelation->setIsInit(true);
            }
        }
    }

    /**
     * Set MediaRelation with entitylocale
     *
     * @param array $interface
     * @param $entity
     */
    public function setEntityLocale(array $interface, $entity)
    {
        if (!$entity->getMediaRelation()) {

            $mediaRelation = new MediaRelation();
            $mediaRelation->setLocale($this->request->get('entitylocale'));
            $mediaRelation->setCategory($interface['name']);
            $entity->setMediaRelation($mediaRelation);

            $this->entityManager->persist($entity);
            $this->entityManager->flush();

            try {
                $this->entityManager->refresh($entity);
            } catch (Exception $exception) {
            }
        }
    }

    /**
     * Synchronize locales MediaRelation[]
     *
     * @param $entity
     * @param Website $website
     * @param array $interface
     */
    public function setMediaRelations($entity, Website $website, array $interface = [])
    {
        $hasMultiple = !empty($interface['configuration']) && property_exists($interface['configuration'], 'mediaMulti')
            ? $interface['configuration']->mediaMulti : false;

        if (!$hasMultiple && method_exists($entity, 'getMediaRelations') && !$entity instanceof Media) {
            $mediasRelations = $entity->getMediaRelations();
            if ($mediasRelations->count() === 0) {
                foreach ($website->getConfiguration()->getAllLocales() as $locale) {
                    $media = new Media();
                    $media->setWebsite($website);
                    $mediaRelation = new MediaRelation();
                    $mediaRelation->setLocale($locale);
                    $mediaRelation->setMedia($media);
                    $entity->addMediaRelation($mediaRelation);
                }
                $this->entityManager->persist($entity);
                $this->entityManager->flush();
            }
            foreach ($mediasRelations as $mediaRelation) {
                $this->synchronizeLocales($website, $interface, $entity, $mediaRelation);
            }
        }
    }

    /**
     * Set Locale MediaRelation
     *
     * @param array $interface
     * @param string $locale
     * @param mixed $entity
     * @param MediaRelation $mediaRelation
     */
    private function setEntity(array $interface, string $locale, $entity, MediaRelation $mediaRelation)
    {
        if (!empty($interface['name'])) {

            $classname = $this->entityManager->getClassMetadata(get_class($entity))->getName();
            $repository = $this->entityManager->getRepository($classname);
            $entityLocaleRelation = $repository->createQueryBuilder('e')->select('e')
                ->leftJoin('e.mediaRelations', 'm')
                ->andWhere('e.id = :id')
                ->andWhere('m.position = :position')
                ->andWhere('m.locale = :locale')
                ->setParameter('position', $mediaRelation->getPosition())
                ->setParameter('locale', $locale)
                ->setParameter('id', $entity->getId())
                ->addSelect('m')
                ->orderBy('m.locale', 'ASC')
                ->getQuery()
                ->getOneOrNullResult();

            $localeRelation = $entityLocaleRelation && !$entityLocaleRelation->getMediaRelations()->isEmpty()
                ? $entityLocaleRelation->getMediaRelations()[0] : NULL;

            if (!$localeRelation && !in_array($locale, $this->localesSet)) {

                $this->localesSet[] = $locale;
                $media = $mediaRelation->getMedia();

                $localeRelation = new MediaRelation();
                $localeRelation->setLocale($locale);
                $localeRelation->setPosition($mediaRelation->getPosition());
                $localeRelation->setMedia($mediaRelation->getMedia());
                $localeRelation->setCategory($mediaRelation->getCategory());
                $entity->addMediaRelation($localeRelation);

                if (!empty($media)) {
                    $this->setI18nMedia($localeRelation, $media);
                }
            }

            if ($entity) {
                $this->entityManager->persist($entity);
                $this->entityManager->flush();
            }

            try {
                $this->entityManager->refresh($entity);
            } catch (Exception $exception) {
            }
        }
    }

    /**
     * Set i18n MediaRelation
     *
     * @param MediaRelation $mediaRelation
     * @param Media $media
     */
    private function setI18nMedia(MediaRelation $mediaRelation, Media $media)
    {
        $i18n = new i18n();
        $i18n->setLocale($mediaRelation->getLocale());
        $i18n->setWebsite($media->getWebsite());
        $i18n->setTitle($media->getName());
        $media->addI18n($i18n);
    }

    /**
     * Set Media
     *
     * @param Media $media
     */
    private function setMedia(Media $media)
    {
        $dbName = str_replace('.' . $media->getExtension(), '', $media->getFilename());
        if ($media->getName() !== $dbName) {
            $isRename = $this->uploader->rename($dbName, $media->getName(), $media->getExtension());
            if ($isRename) {
                $media->setFilename($media->getName() . '.' . $media->getExtension());
            }
        }
    }

    /**
     * Remove Media & files
     *
     * @param Media $media
     * @return object
     */
    public function removeMedia(Media $media)
    {
        $message = '';

        if ($media->getMediaRelations()->isEmpty()) {

            foreach ($media->getMediaScreens() as $mediaScreen) {
                $this->uploader->removeFile($mediaScreen->getFilename());
                $this->entityManager->remove($mediaScreen);
            }

            $this->uploader->removeFile($media->getFilename());
            $this->entityManager->remove($media);

            $this->entityManager->flush();

            return (object)[
                'success' => true,
                'message' => $message
            ];
        }

        return (object)[
            'success' => false,
            'message' => $this->removeMediaMessages($media)
        ];
    }

    /**
     * Get Media message alert
     *
     * @param Media $media
     * @return string
     */
    private function removeMediaMessages(Media $media)
    {
        $excludes = [Website::class, MediaRelation::class, Media::class, Thumb::class];
        $metaData = $this->entityManager->getMetadataFactory()->getAllMetadata();

        $message = $this->translator->trans('Suppression impossible pour le fichier suivant :', [], 'admin') . ' ' . $media->getFilename();
        $message .= '<ul class="mb-0">';

        foreach ($metaData as $data) {

            $classname = $data->getName();

            if ($data->getReflectionClass()->getModifiers() === 0 && !in_array($classname, $excludes)) {
                $entity = new $classname();
                if (method_exists($entity, 'getMediaRelations') || method_exists($entity, 'getMediaRelation')) {

                    $identifier = method_exists($entity, 'getMediaRelations') ? 'mediaRelations' : 'mediaRelation';
                    $repository = $this->entityManager->getRepository($classname);
                    $existing = $repository->createQueryBuilder('e')->select('e')
                        ->leftJoin('e.' . $identifier, 'mr')
                        ->andWhere('mr.media = :media')
                        ->setParameter('media', $media)
                        ->addSelect('mr')
                        ->getQuery()
                        ->getResult();

                    foreach ($existing as $mappingEntity) {
                        $message .= $this->removeMediaMessage($classname, $mappingEntity, $media);
                    }
                }
            }
        }

        $message .= '</ul>';

        return $message;
    }

    /**
     * Get message
     *
     * @param string $classname
     * @param $entity
     * @param Media $media
     * @return string
     */
    private function removeMediaMessage(string $classname, $entity, Media $media)
    {
        $message = '';
        $interface = $this->interfaceHelper->generate($classname);
        $layoutAdminName = $this->getLayoutAdminName($entity);

        if (method_exists($entity, 'getAdminName') && $entity->getAdminName()) {
            if ($layoutAdminName && $entity instanceof Block) {
                $message = '<li>' . $entity->getAdminName() . ' - ' . $this->translator->trans('Mise en page :', [], 'admin') . ' ' . $layoutAdminName . ' - ' . $this->translator->trans('Bloc :', [], 'admin') . ' ' . $entity->getBlockType()->getAdminName() . ' (ID: ' . $entity->getId() . ')</li>';
            } elseif ($layoutAdminName) {
                $message = '<li>' . $entity->getAdminName() . ' - ' . $this->translator->trans('Mise en page :', [], 'admin') . ' ' . $layoutAdminName . '</li>';
            } else {
                $message = '<li>' . $entity->getAdminName() . '</li>';
            }
        } elseif ($entity instanceof Seo) {
            $message = '<li>' . $this->translator->trans('Image de partage', [], 'admin') . '</li>';
        } elseif (method_exists($entity, 'getI18ns')) {
            foreach ($entity->getI18ns() as $i18n) {
                /** @var $i18n i18n */
                if (!empty($i18n->getTitle()) && $i18n->getLocale() === $media->getWebsite()->getConfiguration()->getLocale()) {
                    $message = '<li>' . $i18n->getTitle() . '</li>';
                }
            }
        }

        if (!$message) {

            $translation = $this->translator->trans('singular', [], 'entity_' . $interface['name']);
            $singular = $translation != 'singular' ? $translation : ucfirst($interface['name']);

            if ($layoutAdminName && $entity instanceof Block) {
                $message = '<li>' . $this->translator->trans('Utilisé dans Bloc', [], 'admin') . ' ' . $entity->getBlockType()->getAdminName() . ' (ID : ' . $entity->getId() . ') - ' . $this->translator->trans('Mise en page :', [], 'admin') . ' ' . $layoutAdminName . '</li>';
            } elseif ($layoutAdminName) {
                $message = '<li>' . $this->translator->trans('Utilisé dans', [], 'admin') . ' ' . $singular . ' (ID : ' . $entity->getId() . ') - ' . $this->translator->trans('Mise en page :', [], 'admin') . ' ' . $layoutAdminName . '</li>';
            } else {
                $message = '<li>' . $this->translator->trans('Utilisé dans', [], 'admin') . ' ' . $singular . ' (ID : ' . $entity->getId() . ')</li>';
            }
        }

        return mb_convert_encoding($message, 'UTF-8', 'UTF-8');
    }

    /**
     * Get layout link
     *
     * @param $entity
     * @return string|null
     */
    private function getLayoutAdminName($entity)
    {
        $layout = NULL;

        if ($entity instanceof Zone) {
            $layout = $entity->getLayout();
        }

        if ($entity instanceof Block) {
            $layout = $entity->getCol()->getZone()->getLayout();
        }

        $adminName = $layout instanceof Layout ? preg_replace('/\x03/', ' ', $layout->getAdminName()) : NULL;

        return $adminName ? ucfirst(strtolower($adminName)) : NULL;
    }
}