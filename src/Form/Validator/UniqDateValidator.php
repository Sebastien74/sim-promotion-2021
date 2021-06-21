<?php

namespace App\Form\Validator;

use App\Helper\Core\InterfaceHelper;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * UniqDateValidator
 *
 * Check if URL already exist
 *
 * @property TranslatorInterface $translator
 * @property EntityManagerInterface $entityManager
 * @property InterfaceHelper $interfaceHelper
 * @property Request $request
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class UniqDateValidator extends ConstraintValidator
{
    private $translator;
    private $entityManager;
    private $interfaceHelper;
    private $request;

    /**
     * UniqDateValidator constructor.
     *
     * @param TranslatorInterface $translator
     * @param EntityManagerInterface $entityManager
     * @param InterfaceHelper $interfaceHelper
     * @param RequestStack $requestStack
     */
    public function __construct(
        TranslatorInterface $translator,
        EntityManagerInterface $entityManager,
        InterfaceHelper $interfaceHelper,
        RequestStack $requestStack)
    {
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->interfaceHelper = $interfaceHelper;
        $this->request = $requestStack->getMasterRequest();
    }

    /**
     * Validate
     *
     * @param DateTime|string|null $date
     * @param Constraint $constraint
     * @return bool
     */
    public function validate($date, Constraint $constraint)
    {
        if (!$date instanceof DateTime) {
            return false;
        }

        /** @var $form Form */
        $form = $this->context->getRoot();
        $parentEntity = is_object($form) && method_exists($form, 'getNormData') ? $form->getNormData() : NULL;

        if ($parentEntity) {

            $interface = is_object($parentEntity) ? $this->interfaceHelper->generate(get_class($parentEntity)) : [];
            $masterField = !empty($interface['masterField']) ? $interface['masterField']
                : (is_object($parentEntity) && method_exists($parentEntity, 'getWebsite') ? 'website' : NULL);
            $masterFieldId = $masterField && $this->request->get($masterField) ? $this->request->get($masterField) : NULL;
            $existingStart = $this->existing('publicationStart', $date, $parentEntity, $masterField, $masterFieldId);
            $existingEnd = $this->existing('publicationStart', $date, $parentEntity, $masterField, $masterFieldId);

            if ($existingStart || $existingEnd) {
                return $this->context->buildViolation(rtrim($this->translator->trans("Cette date existe déjà !!", [], 'validators_cms'), '<br/>'))
                    ->addViolation();
            }
        }
    }

    /**
     * Check if existing
     *
     * @param string $fieldName
     * @param DateTime $date
     * @param mixed|null $parentEntity
     * @param null $masterField
     * @param null $masterFieldId
     * @return bool
     * @throws NonUniqueResultException
     */
    private function existing(string $fieldName, DateTime $date, $parentEntity = NULL, $masterField = NULL, $masterFieldId = NULL)
    {
        $repository = $this->entityManager->getRepository(get_class($parentEntity));

        $queryBuilder = $repository->createQueryBuilder('e');
        $queryBuilder->andWhere('e.' . $fieldName . ' BETWEEN :start AND :end');
        $queryBuilder->setParameter('start', $date->format('Y-m-d') . ' 00:00:00');
        $queryBuilder->setParameter('end', $date->format('Y-m-d') . ' 23:23:59');

        if ($masterField && $masterFieldId) {
            $queryBuilder->andWhere('e.' . $masterField . '= :masterFiled');
            $queryBuilder->setParameter('masterFiled', $masterFieldId);
        }

        $existing = $queryBuilder->getQuery()->getOneOrNullResult();

        if ($existing && $existing->getId() !== $parentEntity->getId()) {
            return true;
        }

        return false;
    }
}