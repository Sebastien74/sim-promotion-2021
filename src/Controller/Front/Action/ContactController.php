<?php

namespace App\Controller\Front\Action;

use App\Controller\Front\FrontController;
use App\Entity\Module\Contact\Contact;
use App\Entity\Core\Website;
use App\Entity\Layout\Block;
use App\Repository\Module\Contact\ContactRepository;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * ContactController
 *
 * Front Contact renders
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ContactController extends FrontController
{
    /**
     * Contact view
     *
     * @Route("/front/contact/view/{filter}", methods={"GET"}, name="front_contact_view", schemes={"%protocol%"})
     *
     * @param Request $request
     * @param ContactRepository $contactRepository
     * @param Website $website
     * @param Block|null $block
     * @param null $filter
     * @return Response
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function contact(Request $request, ContactRepository $contactRepository, Website $website, Block $block = NULL, $filter = NULL)
    {
        if (!$filter) {
            return new Response();
        }

        /** @var Contact $contact */
        $contact = $contactRepository->findOneByFilter($website, $filter, $request->getLocale());

        if (!$contact) {
            return new Response();
        }

        $configuration = $website->getConfiguration();
        $websiteTemplate = $configuration->getTemplate();
        $entity = $block instanceof Block ? $block : $contact;
        $entity->setUpdatedAt($contact->getUpdatedAt());

        return $this->cache('front/' . $websiteTemplate . '/actions/contact/view.html.twig', $entity, [
            'configuration' => $configuration,
            'websiteTemplate' => $websiteTemplate,
            'website' => $website,
            'thumbConfiguration' => $this->thumbConfiguration($website, Contact::class, 'view'),
            'contact' => $contact
        ], $configuration->getFullCache());
    }
}