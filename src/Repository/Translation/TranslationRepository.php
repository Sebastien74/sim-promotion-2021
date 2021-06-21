<?php

namespace App\Repository\Translation;

use App\Entity\Translation\Translation;
use App\Entity\Translation\TranslationDomain;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * TranslationRepository
 *
 * @method Translation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Translation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Translation[]    findAll()
 * @method Translation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TranslationRepository extends ServiceEntityRepository
{
    /**
     * TranslationRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Translation::class);
    }

    /**
     * Find by TranslationDomain & content
     *
     * @param TranslationDomain $domain
     * @param string $content
     * @param string $locale
     * @return Translation[]
     */
    public function findByDomainAndContent(TranslationDomain $domain, string $content, string $locale): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.unit', 'u')
            ->andWhere('u.domain = :domain')
            ->andWhere('t.content = :content')
            ->andWhere('t.locale = :locale')
            ->setParameter('domain', $domain)
            ->setParameter('content', $content)
            ->setParameter('locale', $locale)
            ->addSelect('u')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find by TranslationDomain & content
     *
     * @param TranslationDomain $domain
     * @param string $keyName
     * @param string $locale
     * @return Translation[]
     */
    public function findByDomainAndKeyName(TranslationDomain $domain, string $keyName, string $locale): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.unit', 'u')
            ->andWhere('u.domain = :domain')
            ->andWhere('u.keyName = :keyName')
            ->andWhere('t.locale = :locale')
            ->setParameter('domain', $domain)
            ->setParameter('keyName', $keyName)
            ->setParameter('locale', $locale)
            ->addSelect('u')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get Translations by locale
     *
     * @param string $name
     * @param string $locale
     * @return array
     */
    public function findByDomainAndLocale(string $name, string $locale)
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.unit', 'u')
            ->leftJoin('u.domain', 'd')
            ->andWhere('t.locale = :locale')
            ->andWhere('t.content IS NOT NULL')
            ->andWhere('d.name = :name')
            ->setParameter('locale', $locale)
            ->setParameter('name', $name)
            ->addSelect('u')
            ->getQuery()
            ->getResult();
    }
}
