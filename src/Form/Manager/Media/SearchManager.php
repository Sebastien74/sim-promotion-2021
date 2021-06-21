<?php

namespace App\Form\Manager\Media;

use App\Entity\Core\Website;
use App\Entity\Media\Media;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * SearchManager
 *
 * Manage admin search Media[] form
 *
 * @property EntityManagerInterface $entityManager
 * @property bool $isInternalUser
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SearchManager
{
    private $entityManager;
    private $isInternalUser;

    /**
     * SearchManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(EntityManagerInterface $entityManager, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->entityManager = $entityManager;
        $this->isInternalUser = $authorizationChecker->isGranted('ROLE_INTERNAL');
    }

    public function search(Form $form, Website $website)
    {
        $search = method_exists($form, 'getData') && !empty($form->getData()['searchMedia'])
            ? $form->getData()['searchMedia']
            : NULL;

        $queryResults = $this->getQueryResults($website, $search);

        return $this->parse($queryResults);
    }

    /**
     * Get query result
     *
     * @param Website $website
     * @param string|NULL $search
     * @return Media[]|array
     */
    private function getQueryResults(Website $website, string $search = NULL)
    {
        $result = [];
        $repository = $this->entityManager->getRepository(Media::class);
        $statement = $repository->createQueryBuilder('m');
        $statement->select('m')
            ->andWhere('m.website = :website')
            ->andWhere('m.screen = :screen')
            ->setParameter('website', $website)
            ->setParameter('screen', 'desktop');

        if ($search) {

            try {
                $statement->leftJoin('m.i18ns', 'i')
                    ->addSelect('i')
                    ->addSelect("((MATCH_AGAINST(m.filename, :search 'IN BOOLEAN MODE') * 2) + MATCH_AGAINST(i.title, :search 'IN BOOLEAN MODE')) as score")
                    ->setParameter('search', $search . '*')
                    ->having('score > 0')
                    ->orderBy('score', 'DESC');

                $result = $statement->getQuery()->getResult();
            } catch (\Exception $exception) {
                dd($exception);
            }
        } else {
            $result = $statement->getQuery()->getResult();
        }

        return $result;
    }

    /**
     * Parse results and remove Internal media for not allowed User
     *
     * @param array $queryResults
     * @return array
     */
    private function parse(array $queryResults)
    {
        $result = [];

        foreach ($queryResults as $media) {
            $media = is_array($media) && !empty($media[0]) ? $media[0] : $media;
            if ($this->isInternalUser || !$this->isInternalUser && $media instanceof Media && !$media->getFolder() || !$this->isInternalUser && $media instanceof Media && !$media->getFolder()->getWebmaster()) {
                $result[] = $media;
            }
        }

        return $result;
    }
}