<?php

namespace App\Service\Delete;

use App\Entity\Module\Form\ContactForm;
use App\Entity\Module\Form\ContactStepForm;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * ContactDeleteService
 *
 * Manage Contact Form deletion
 *
 * @property Request $request
 * @property EntityManagerInterface $entityManager
 * @property KernelInterface $kernel
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ContactDeleteService
{
    private $request;
    private $entityManager;
    private $kernel;

    /**
     * ContactDeleteService constructor.
     *
     * @param KernelInterface $kernel
     * @param RequestStack $requestStack
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(KernelInterface $kernel, RequestStack $requestStack, EntityManagerInterface $entityManager)
    {
        $this->kernel = $kernel;
        $this->request = $requestStack->getMasterRequest();
        $this->entityManager = $entityManager;
    }

    /**
     * Execute service
     */
    public function execute()
    {
        $requestName = $this->request->get('formcontact') ? 'formcontact' : ($this->request->get('contactstepform') ? 'contactstepform' : NULL);
        if($requestName) {
            $classname = $requestName === 'formcontact' ? ContactForm::class : ContactStepForm::class;
            $contact = $this->entityManager->getRepository($classname)->find($this->request->get($requestName));
            $this->deleteAttachments($contact);
        }
    }

    /**
     * Remove old by day limit
     *
     * @param int $limit
     * @throws Exception
     */
    public function removeOldCmd(int $limit = 4)
    {
        if ($this->kernel->getEnvironment() === 'prod') {
            $application = new Application($this->kernel);
            $application->setAutoExit(false);
            $input = new ArrayInput([
                'command' => 'contacts:remove',
                'limit' => $limit
            ]);
            $output = new BufferedOutput();
            $application->run($input, $output);
        }
    }

    /**
     * Remove old by day limit
     *
     * @param int $limit
     */
    public function removeOld(int $limit = 4)
    {
        $datetime = new \DateTime('now');
        $datetime->modify('- ' . $limit . ' days');

        $flush = false;
        $contacts = $this->entityManager->getRepository(ContactForm::class)
            ->createQueryBuilder('c')
            ->andWhere('c.createdAt < ' . ':createdAt')
            ->setParameter('createdAt', $datetime->format('Y-m-d') . ' 00:00:00')
            ->getQuery()->getResult();

        foreach ($contacts as $contact) {
            $this->deleteAttachments($contact);
            $this->entityManager->remove($contact);
            $flush = true;
        }

        if($flush) {
            $this->entityManager->flush();
        }
    }

    /**
     * Attachments deletion
     *
     * @param ContactForm|ContactStepForm|null $contact
     */
    private function deleteAttachments($contact = NULL)
    {
        if($contact) {

            $filesystem = new Filesystem();
            $publicDirname = $this->kernel->getProjectDir() . '/public';

            foreach ($contact->getContactValues() as $value) {
                if(preg_match('/uploads\/emails\//', $value->getValue())) {
                    $fileDirname = $publicDirname . $value->getValue();
                    if($filesystem->exists($fileDirname)) {
                        $filesystem->remove($fileDirname);
                    }
                }
            }
        }
    }
}