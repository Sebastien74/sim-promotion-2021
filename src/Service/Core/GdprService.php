<?php

namespace App\Service\Core;

use App\Entity\Module\Form\ContactForm;
use App\Entity\Module\Form\ContactStepForm;
use App\Entity\Module\Newsletter\Email;
use App\Entity\Core\Website;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * GdprService
 *
 * Manage Gdpr process
 *
 * @property EntityManagerInterface $entityManager
 * @property KernelInterface $kernel
 * @property CronSchedulerService $cronSchedulerService
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class GdprService
{
    private $entityManager;
    private $kernel;
    private $cronSchedulerService;

    /**
     * GdprService constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param KernelInterface $kernel
     * @param CronSchedulerService $cronSchedulerService
     */
    public function __construct(EntityManagerInterface $entityManager, KernelInterface $kernel, CronSchedulerService $cronSchedulerService)
    {
        $this->entityManager = $entityManager;
        $this->kernel = $kernel;
        $this->cronSchedulerService = $cronSchedulerService;
    }

    /**
     * To remove old data
     *
     * @param Website $website
     * @param InputInterface|null $input
     * @param string|null $command
     * @throws Exception
     */
    public function removeData(Website $website, InputInterface $input = NULL, string $command = NULL)
    {
        if ($website->getConfiguration()->getGdprFrequency() > 0) {

            $namespaces = [ContactForm::class, ContactStepForm::class, Email::class];
            $datetime = new DateTime();
            $interval = new DateInterval('P' . $website->getConfiguration()->getGdprFrequency() . 'D');
            $datetime->sub($interval);

            foreach ($namespaces as $namespace) {

                $entities = $this->entityManager->getRepository($namespace)->createQueryBuilder('e')
                    ->andWhere('e.createdAt <= :datetime')
                    ->setParameter('datetime', $datetime)
                    ->getQuery()
                    ->getResult();

                foreach ($entities as $entity) {
                    $this->removeAttachments($entity);
                    $this->entityManager->remove($entity);
                    $this->entityManager->flush();
                }

                $this->cronSchedulerService->logger('[OK] ' . $namespace . ' successfully cleared.', $input);
            }

            $this->cronSchedulerService->logger('[EXECUTED] ' . $command, $input);
        }
    }

    /**
     * Remove attachments
     *
     * @param mixed $entity
     */
    private function removeAttachments($entity)
    {
        $filesystem = new Filesystem();

        if ($entity instanceof ContactForm || $entity instanceof ContactStepForm) {
            foreach ($entity->getContactValues() as $value) {
                if (preg_match('/public\/uploads/', $value->getValue())) {
                    $formId = $entity instanceof ContactForm ? $entity->getForm()->getId() : $entity->getStepform()->getId();
                    $formType = $entity instanceof ContactForm ? 'forms' : 'steps-forms';
                    $website = $entity instanceof ContactForm ? $entity->getForm()->getWebsite() : $entity->getStepform()->getWebsite();
                    $fileDirname = $this->kernel->getProjectDir() . '/public/uploads/' . $website->getUploadDirname() . '/emails/' . $formType . '/' . $formId . '/contacts/' . $entity->getId() . '/';
                    $fileDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $fileDirname);
                    if ($filesystem->exists($fileDirname)) {
                        $filesystem->remove($fileDirname);
                    }
                }
            }
        }
    }
}