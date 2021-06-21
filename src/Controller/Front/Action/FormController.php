<?php

namespace App\Controller\Front\Action;

use App\Controller\Front\FrontController;
use App\Entity\Module\Contact\Contact;
use App\Entity\Module\Form\Calendar;
use App\Entity\Module\Form\Configuration;
use App\Entity\Module\Form\ContactForm;
use App\Entity\Module\Form\ContactStepForm;
use App\Entity\Module\Form\Form;
use App\Entity\Module\Form\StepForm;
use App\Entity\Core\Website;
use App\Entity\Layout\Block;
use App\Entity\Layout\Page;
use App\Entity\Seo\Url;
use App\Form\Manager\Front\FormCalendarManager;
use App\Form\Manager\Front\FormManager;
use App\Form\Type\Module\Form\FrontCalendarType;
use App\Form\Type\Module\Form\FrontType;
use App\Repository\Module\Form\FormRepository;
use App\Repository\Module\Form\StepFormRepository;
use App\Service\Core\CacheService;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

/**
 * FormController
 *
 * Front Form renders
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FormController extends FrontController
{
    /**
     * View Form
     *
     * @Route("/front/from/view/{website}/{url}/{filter}", methods={"GET", "POST"}, name="front_form_view", schemes={"%protocol%"})
     *
     * @param Request $request
     * @param FormRepository $formRepository
     * @param FormManager $formManager
     * @param Website $website
     * @param Url $url
     * @param Block|null $block
     * @param null|string|int $filter
     * @return Response
     * @throws Exception
     */
    public function view(
        Request $request,
        FormRepository $formRepository,
        FormManager $formManager,
        Website $website,
        Url $url,
        Block $block = NULL,
        $filter = NULL)
    {
        if (!$filter) {
            return new Response();
        }

        /** @var Form $entity */
        $entity = $formRepository->findOneByFilter($website, $request->getLocale(), $filter);
        $contact = $formManager->getContact();

        if (!$entity) {
            return new Response();
        }

        $template = $website->getConfiguration()->getTemplate();
        $form = $this->createForm(FrontType::class, NULL, ['form_data' => $entity]);
        $form->handleRequest($request);

        return $this->getRender($form, $formManager, [
            'request' => $request,
            'websiteTemplate' => $template,
            'website' => $website,
            'contact' => $contact,
            'url' => $url,
            'filter' => $filter,
            'block' => $block,
            'entity' => $entity,
            'configuration' => $entity->getConfiguration()
        ]);
    }

    /**
     * View Form
     *
     * @Route("/front/from/steps/view/{website}/{url}/{filter}", methods={"GET", "POST"}, name="front_formstep_view", schemes={"%protocol%"})
     *
     * @param Request $request
     * @param StepFormRepository $stepFormRepository
     * @param FormManager $formManager
     * @param Website $website
     * @param Url $url
     * @param Block|null $block
     * @param string|int|null $filter
     * @return JsonResponse|Response
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function step(
        Request $request,
        StepFormRepository $stepFormRepository,
        FormManager $formManager,
        Website $website,
        Url $url,
        Block $block = NULL,
        $filter = NULL)
    {
        if (!$filter) {
            return new Response();
        }

        /** @var StepForm $entity */
        $entity = $stepFormRepository->findOneByFilter($website, $request->getLocale(), $filter);
        $contact = $formManager->getContact();

        if (!$entity) {
            return new Response();
        }

        $template = $website->getConfiguration()->getTemplate();
        $form = $this->createForm(FrontType::class, NULL, ['form_data' => $entity]);
        $form->handleRequest($request);

        return $this->getRender($form, $formManager, [
            'request' => $request,
            'websiteTemplate' => $template,
            'website' => $website,
            'contact' => $contact,
            'url' => $url,
            'filter' => $filter,
            'block' => $block,
            'entity' => $entity,
            'configuration' => $entity->getConfiguration()
        ]);
    }

    /**
     * View calendar
     *
     * @Route("/front/from/calendar/view/{block}", methods={"GET", "POST"}, name="front_form_calendar_view", schemes={"%protocol%"})
     *
     * @param Request $request
     * @param FormCalendarManager $calendarManager
     * @param FormManager $formManager
     * @param Block|null $block
     * @return Response
     * @throws Exception
     */
    public function calendar(Request $request, FormCalendarManager $calendarManager, FormManager $formManager, Block $block = NULL)
    {
        $website = $this->getWebsite($request);
        $websiteTemplate = $website->getConfiguration()->getTemplate();
        $contact = $formManager->getContact();
        $calendar = $calendarManager->setCalendar($website, $contact);
        $form = $contact instanceof Contact ? $contact->getForm() : $calendar->getForm();
        $dates = $calendarManager->getDates($contact);
        $formCalendar = $this->createForm(FrontCalendarType::class, NULL, ['dates' => $dates]);
        $formCalendar->handleRequest($request);
        $register = $calendarManager->register($formCalendar, $contact);
        $calendars = !$contact ? $this->entityManager->getRepository(Calendar::class)->findBy(['form' => $form], ['position' => 'ASC']) : NULL;

        $template = 'front/' . $websiteTemplate . '/actions/form/calendar/calendar.html.twig';
        if ($register === 'success') {
            $template = 'front/' . $websiteTemplate . '/actions/form/calendar/calendar-success.html.twig';
        }

        $arguments = [
            'websiteTemplate' => $websiteTemplate,
            'website' => $website,
            'block' => $block,
            'register' => $register,
            'calendar' => $calendar,
            'calendars' => $calendars,
            'token' => !empty($_GET['token']) ? $_GET['token'] : NULL,
            'startDate' => $request->get('startDate'),
            'dates' => $dates,
            'formCalendar' => $formCalendar->createView(),
            'form' => $form,
            'contact' => $contact
        ];

        if ($request->get('ajax')) {
            return new JsonResponse(['html' => $this->renderView($template, $arguments), 'slotDate' => $register, 'calendar' => $calendar->getId()]);
        }

        return $this->cache($template, NULL, $arguments);
    }

    /**
     * Success view
     *
     * @param FormManager $formManager
     * @param Website $website
     * @return Response
     * @throws Exception
     */
    public function success(FormManager $formManager, Website $website)
    {
        $template = $website->getConfiguration()->getTemplate();
        $contact = $formManager->getContact();

        if (!$contact) {
            return new Response();
        }

        $form = $contact instanceof ContactStepForm ? $contact->getStepform() : ($contact instanceof ContactForm ? $contact->getForm() : NULL);

        $fields = [];
        if ($contact && method_exists($contact, 'getContactValues')) {
            foreach ($contact->getContactValues() as $value) {
                if ($value->getConfiguration()->getSlug()) {
                    $fields[$value->getConfiguration()->getSlug()] = $value;
                }
            }
        }

        return $this->cache('front/' . $template . '/actions/form/success.html.twig', NULL, [
            'websiteTemplate' => $template,
            'website' => $website,
            'fields' => $fields,
            'form' => $form,
            'contact' => $contact
        ]);
    }

    /**
     * Get render view
     *
     * @param FormInterface $form
     * @param FormManager $formManager
     * @param array $arguments
     * @return JsonResponse|Response
     * @throws Exception
     */
    private function getRender(FormInterface $form, FormManager $formManager, array $arguments)
    {
        /** @var Form $entity */
        $entity = $arguments['entity'];
        $request = $arguments['request'];
        $configuration = $arguments['configuration'];
        $session = new Session();

        /** @var Url $url */
        $url = $arguments['url'];
        $template = $arguments['entity'] instanceof Form ? 'view' : 'step-form';

        if (!$configuration->getAjax() && $form->isSubmitted()) {

            if (!$form->isValid()) {
                $formManager->errors($form);
                return $this->redirectToRoute('front_index', ['url' => $url->getCode()]);
            } else {
                $contact = $formManager->success($entity, $form);
                $arguments['contact'] = $contact;
                if ($configuration->getThanksModal()) {
                    $session->set('form_success', 'form-thanks-modal-' . $arguments['entity']->getId());
                }
                return $this->getRedirection($arguments['request'], $url, $configuration, $contact);
            }

        } elseif ($configuration->getAjax() && $form->isSubmitted()) {

            $arguments['form'] = $form->createView();

            $contact = NULL;
            $hasSuccess = $request->get('advancement') === 'finished' || !$request->get('advancement');

            if ($form->isValid() && $hasSuccess) {
                $contact = $formManager->success($entity, $form);
                $arguments['contact'] = $contact;
            } else {
                $formManager->errors($form);
            }

            $hasContact = $contact instanceof ContactForm || $contact instanceof ContactStepForm;

            return new JsonResponse([
                'success' => $form->isValid(),
                'showModal' => $configuration->getThanksModal(),
                'dataId' => $hasContact ? $contact->getId() : NULL,
                'token' => $hasContact ? $contact->getToken() : NULL,
                'redirection' => $this->getRedirection($arguments['request'], $url, $configuration, $contact),
                'html' => $this->renderView('core/render.html.twig', [
                    'html' => $this->subscriber->get(CacheService::class)->parse($this->renderView('front/' . $arguments['websiteTemplate'] . '/actions/form/' . $template . '.html.twig', $arguments))
                ])
            ]);
        }

        $arguments['form'] = $form->createView();

        return $this->cache('front/' . $arguments['websiteTemplate'] . '/actions/form/' . $template . '.html.twig', $entity, $arguments);
    }

    /**
     * Get redirection
     *
     * @param Request $request
     * @param Url $url
     * @param Configuration $configuration
     * @param ContactForm|ContactStepForm|null $contact
     * @return RedirectResponse|string
     */
    private function getRedirection(Request $request, Url $url, Configuration $configuration, $contact = NULL)
    {
        if (!$contact) {
            return false;
        }

        $hasAjax = $configuration->getAjax();
        $urlCode = $hasAjax ? NULL : $url->getCode();
        $pageRedirection = $configuration->getPageRedirection();

        if ($pageRedirection instanceof Page) {
            foreach ($pageRedirection->getUrls() as $url) {
                if ($url->getLocale() === $request->getLocale() && $url->getIsOnline() && $url->getCode()) {
                    $urlCode = $url->getCode();
                    break;
                }
            }
        }

        if ($hasAjax) {
            return $urlCode ? $this->generateUrl('front_index', ['url' => $urlCode, 'token' => $contact->getToken()]) : NULL;
        }

        return $this->redirectToRoute('front_index', ['url' => $urlCode, 'token' => $contact->getToken()]);
    }
}