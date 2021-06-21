<?php

namespace App\Helper\Admin;

use App\Entity\Core\Website;
use App\Form\Type\Core\DefaultType;
use App\Helper\Core\InterfaceHelper;
use App\Repository\Core\WebsiteRepository;
use App\Service\Core\SubscriberService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * FormDuplicateHelper
 *
 * To manage admin form duplication
 *
 * @property Request $request
 * @property InterfaceHelper $interfaceHelper
 * @property EntityManagerInterface $entityManager
 * @property FormFactoryInterface $formFactory
 * @property array $interface
 * @property WebsiteRepository $websiteRepository
 * @property TranslatorInterface $translator
 * @property SubscriberService $serviceSubscriber
 * @property Website $website
 * @property mixed $entityToDuplicate
 * @property mixed $entity
 * @property Form $form
 * @property bool $isSubmitted
 * @property bool $isValid
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FormDuplicateHelper
{
    private $request;
    private $interfaceHelper;
    private $entityManager;
    private $formFactory;
    private $interface;
    private $websiteRepository;
    private $translator;
    private $serviceSubscriber;
    private $website;
    private $entityToDuplicate;
    private $entity;
    private $form;
    private $isSubmitted;
    private $isValid;

    /**
     * FormDuplicateHelper constructor.
     *
     * @param InterfaceHelper $interfaceHelper
     * @param EntityManagerInterface $entityManager
     * @param FormFactoryInterface $formFactory
     * @param WebsiteRepository $websiteRepository
     * @param TranslatorInterface $translator
     * @param SubscriberService $serviceSubscriber
     */
    public function __construct(
        InterfaceHelper $interfaceHelper,
        EntityManagerInterface $entityManager,
        FormFactoryInterface $formFactory,
        WebsiteRepository $websiteRepository,
        TranslatorInterface $translator,
        SubscriberService $serviceSubscriber)
    {
        $this->interfaceHelper = $interfaceHelper;
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
        $this->websiteRepository = $websiteRepository;
        $this->translator = $translator;
        $this->serviceSubscriber = $serviceSubscriber;
    }

    /**
     * Execute FormDuplicateHelper
     *
     * @param Request $request
     * @param string|NULL $formType
     * @param string $classname
     * @param array $options
     * @param object|null $formManager
     */
    public function execute(Request $request, string $formType = NULL, string $classname, array $options = [], $formManager = NULL)
    {
        $this->request = $request;

        $this->setInterface($classname);
        $this->setWebsite();
        $this->setEntityToDuplicate($classname);
        $this->setEntity();
        $this->setForm($formType, $options);
        $this->submit($formManager);
    }

    /**
     * Set Interface
     *
     * @param string $classname
     */
    public function setInterface(string $classname): void
    {
        $this->interface = $this->interfaceHelper->generate($classname);
    }

    /**
     * Get Interface
     *
     * @return array
     */
    public function getInterface(): array
    {
        return $this->interface;
    }

    /**
     * Set Website
     */
    public function setWebsite(): void
    {
        $this->website = $this->websiteRepository->find($this->request->get('website'));
    }

    /**
     * Get Entity to duplicate
     */
    public function getEntityToDuplicate()
    {
        return $this->entityToDuplicate;
    }

    /**
     * Set Entity to duplicate
     *
     * @param $classname
     */
    public function setEntityToDuplicate($classname): void
    {
        $this->entityToDuplicate = $this->entityManager->getRepository($classname)->find($this->request->get($this->interface['name']));
    }

    /**
     * Get Entity
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set Entity
     */
    public function setEntity(): void
    {
        $this->entity = new $this->interface['entity']();
    }

    /**
     * Get isSubmitted
     */
    public function isSubmitted()
    {
        return $this->isSubmitted;
    }

    /**
     * Set isSubmit
     *
     * @param bool $isSubmitted
     */
    public function setIsSubmitted(bool $isSubmitted): void
    {
        $this->isSubmitted = $isSubmitted;
    }

    /**
     * Get isValid
     */
    public function isValid()
    {
        return $this->isValid;
    }

    /**
     * Set isValid
     *
     * @param bool $isValid
     */
    public function setIsValid(bool $isValid): void
    {
        $this->isValid = $isValid;
    }

    /**
     * Get Form
     *
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Set Form
     *
     * @param string|null $formType
     * @param array $options
     */
    public function setForm(string $formType = NULL, array $options): void
    {
        $formType = !empty($formType) ? $formType : DefaultType::class;
        $options['duplicate_entity'] = $this->entityToDuplicate;
        $this->form = $this->formFactory->create($formType, $this->entity, $options);
    }

    /**
     * Form submission process
     *
     * @param null $formManager
     */
    public function submit($formManager = NULL)
    {
        $this->form->handleRequest($this->request);

        $this->setIsSubmitted(false);
        $this->setIsValid(false);

        if ($this->form->isSubmitted() && $this->form->isValid()) {

            if (!$formManager) {
                throw new HttpException(500, $this->translator->trans('Manager non renseigné !!', [], 'admin'));
            }

            $manager = $this->serviceSubscriber->get($formManager);
            if (method_exists($manager, 'execute')) {
                $manager->execute($this->form->getData(), $this->website, $this->form);
            } else {
                throw new HttpException(500, $this->translator->trans("La fonction execute() n'existe pas dans votre manager", [], 'admin'));
            }

            $this->setIsSubmitted(true);
            $this->setIsValid(true);
        } elseif ($this->form->isSubmitted() && !$this->form->isValid()) {
            $this->setIsSubmitted(true);
            $this->setIsValid(false);
        }
    }
}