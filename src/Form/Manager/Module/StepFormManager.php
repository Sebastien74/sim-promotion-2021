<?php

namespace App\Form\Manager\Module;

use App\Entity\Module\Form\Configuration;
use App\Entity\Module\Form\StepForm;
use App\Entity\Core\Website;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * StepFormManager
 *
 * Manage admin StepForm form
 *
 * @property EntityManagerInterface $entityManager
 * @property Request $request
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class StepFormManager
{
    private $entityManager;
    private $request;

    /**
     * StepFormManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param RequestStack $requestStack
     */
    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack)
    {
        $this->entityManager = $entityManager;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @prePersist
     *
     * @param StepForm $stepForm
     * @param Website $website
     * @throws Exception
     */
    public function prePersist(StepForm $stepForm, Website $website)
    {
        $configuration = new Configuration();
        $configuration->setSecurityKey(crypt(random_bytes(10), 'rl'));
        $configuration->setReceivingEmails(['contact@' . $this->request->getHost()]);
        $configuration->setSendingEmail('no-reply@' . $this->request->getHost());
        $configuration->setAjax(true);
        $stepForm->setConfiguration($configuration);

        $this->entityManager->persist($stepForm);
    }
}