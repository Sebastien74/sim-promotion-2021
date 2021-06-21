<?php

namespace App\Form\Validator;

use App\Repository\Module\Newsletter\CampaignRepository;
use App\Repository\Module\Newsletter\EmailRepository;
use App\Repository\Core\WebsiteRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * UniqEmailCampaignValidator
 *
 * Check if Newsletter email already exist
 *
 * @property Request $request
 * @property WebsiteRepository $websiteRepository
 * @property CampaignRepository $campaignRepository
 * @property EmailRepository $emailRepository
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class UniqEmailCampaignValidator extends ConstraintValidator
{
    private $request;
    private $websiteRepository;
    private $campaignRepository;
    private $emailRepository;
    private $translator;

    /**
     * UniqEmailCampaignValidator constructor.
     *
     * @param RequestStack $requestStack
     * @param WebsiteRepository $websiteRepository
     * @param CampaignRepository $campaignRepository
     * @param EmailRepository $emailRepository
     * @param TranslatorInterface $translator
     */
    public function __construct(
        RequestStack $requestStack,
        WebsiteRepository $websiteRepository,
        CampaignRepository $campaignRepository,
        EmailRepository $emailRepository,
        TranslatorInterface $translator)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->websiteRepository = $websiteRepository;
        $this->campaignRepository = $campaignRepository;
        $this->emailRepository = $emailRepository;
        $this->translator = $translator;
    }

    /**
     * Validate
     *
     * @param string $email
     * @param Constraint $constraint
     * @throws NonUniqueResultException
     */
    public function validate($email, Constraint $constraint)
    {
        $websiteRequest = $this->request->get('website');
        $campaignRequest = $this->request->get('filter');

        if ($websiteRequest && $campaignRequest) {

            $website = $this->websiteRepository->find($websiteRequest);
            $campaign = $this->campaignRepository->findOneByFilter($website, $this->request->getLocale(), $campaignRequest);
            $existingEmail = $this->emailRepository->findOneBy([
                'email' => $email,
                'campaign' => $campaign
            ]);

            if ($existingEmail) {
                $message = $this->translator->trans('Cet email existe déjà !', [], 'validators_cms');
                $this->context->buildViolation($message)
                    ->addViolation();
            }
        }
    }
}