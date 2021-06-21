<?php

namespace App\Form\Manager\Seo;

use App\Entity\Core\Website;
use App\Entity\Seo\Seo;
use App\Entity\Seo\SessionUrl;
use App\Entity\Seo\Url;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\Form\Form;

/**
 * UrlManager
 *
 * Manage Seo Url admin form
 *
 * @property EntityManagerInterface $entityManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class UrlManager
{
    private $entityManager;

    /**
     * UrlManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Post Urls
     *
     * @param Form $form
     * @param Website $website
     * @throws NonUniqueResultException
     */
    public function post(Form $form, Website $website)
    {
        $entity = $form->getData();

        if (method_exists($entity, 'getUrls')) {

            if ($entity->getUrls()->count() === 0) {
                $url = new Url();
                $url->setLocale($website->getConfiguration()->getLocale());
                $entity->addUrl($url);
            }

            foreach ($entity->getUrls() as $url) {
                $this->addUrl($url, $website, $entity);
            }
        }
    }

    /**
     * Add Url
     *
     * @param Url $url
     * @param Website $website
     * @param mixed $entity
     * @throws NonUniqueResultException
     */
    public function addUrl(Url $url, Website $website, $entity)
    {
        $defaultLocale = $website->getConfiguration()->getLocale();

        $code = !$url->getCode() && $url->getLocale() === $defaultLocale && method_exists($entity, 'getAdminName')
            ? Urlizer::urlize($entity->getAdminName())
            : Urlizer::urlize($url->getCode());
        $url->setCode($code);

        if (method_exists($entity, 'getInFill') && $entity->getInFill()) {
            $code = NULL;
        }

        if ($code && $url->getLocale() === $defaultLocale) {
            $existing = $this->getExistingUrl($url, $website, $entity);
            $code = $existing && $existing->getId() !== $url->getId() ? $code . '-' . uniqid() : $code;
            $url->setCode($code);
        }

        $url->setWebsite($website);

        if (!$url->getSeo()) {
            $seo = new Seo();
            $seo->setUrl($url);
            $url->setSeo($seo);
            $this->entityManager->persist($seo);
        }

        if (!$website) {
            $url->setWebsite($website);
        }

        if (method_exists($entity, 'getInfill') && $entity->getInfill()) {
            $url->setIsOnline(false);
        }

        $this->entityManager->persist($url);
    }

    /**
     * Synchronize locale Url
     *
     * @param mixed $entity
     * @param Website|null $website
     */
    public function synchronizeLocales($entity, Website $website = NULL)
    {
        if ($website && method_exists($entity, 'getUrls') && $entity->getUrls()->count() > 0) {

            foreach ($website->getConfiguration()->getAllLocales() as $locale) {

                $existing = false;
                foreach ($entity->getUrls() as $url) {
                    /** @var Url $url */
                    if ($url->getLocale() === $locale) {
                        $existing = true;
                    }
                }

                if (!$existing) {
                    $url = new Url();
                    $url->setLocale($locale);
                    $entity->addUrl($url);
                    $this->entityManager->persist($entity);
                    $this->entityManager->flush();
                    $this->entityManager->refresh($entity);
                }
            }
        }
    }

    /**
     * Get existing Url
     *
     * @param Url $url
     * @param Website $website
     * @param $entity
     * @param null $classname
     * @return null/Url
     * @throws NonUniqueResultException
     */
    public function getExistingUrl(Url $url, Website $website, $entity, $classname = NULL): ?Url
    {
        if ($entity instanceof Seo) {
            $metasData = $this->entityManager->getMetadataFactory()->getAllMetadata();
            foreach ($metasData as $metaData) {
                $classname = $metaData->getName();
                $baseEntity = $metaData->getReflectionClass()->getModifiers() === 0 ? new $classname() : NULL;
                if (!$baseEntity instanceof SessionUrl && $baseEntity && method_exists($baseEntity, 'getUrls')) {
                    $entity = $this->entityManager->getRepository($classname)->createQueryBuilder('e')
                        ->leftJoin('e.urls', 'u')
                        ->andWhere('u.id = :id')
                        ->setParameter('id', $url->getId())
                        ->addSelect('u')
                        ->getQuery()
                        ->getOneOrNullResult();
                    if($entity) {
                        break;
                    }
                }
            }
        }

        $locale = $url->getLocale();
        $classname = $this->entityManager->getClassMetadata(get_class($entity))->getName();
        $findEntity = $this->entityManager->getRepository($classname)->createQueryBuilder('e')
            ->leftJoin('e.urls', 'u')
            ->andWhere('u.code = :code')
            ->andWhere('u.locale = :locale')
            ->andWhere('u.website = :website')
            ->setParameter('code', Urlizer::urlize($url->getCode()))
            ->setParameter('locale', $locale)
            ->setParameter('website', $website)
            ->addSelect('u')
            ->getQuery()
            ->getOneOrNullResult();

        if (is_object($findEntity) && method_exists($findEntity, 'getUrls')) {
            foreach ($findEntity->getUrls() as $url) {
                /** @var Url $url */
                if ($url->getLocale() === $locale) {
                    return $url;
                }
            }
        }

        return NULL;
    }
}