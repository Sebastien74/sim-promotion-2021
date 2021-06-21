<?php

namespace App\Form\Validator;

use App\Entity\Seo as SeoEntities;
use App\Form\Manager\Seo\UrlManager;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * UniqUrlValidator
 *
 * Check if URL already exist
 *
 * @property TranslatorInterface $translator
 * @property EntityManagerInterface $entityManager
 * @property UrlManager $urlManager
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class UniqUrlValidator extends ConstraintValidator
{
    private $translator;
    private $entityManager;
    private $urlManager;

    /**
     * UniqUrlValidator constructor.
     *
     * @param TranslatorInterface $translator
     * @param EntityManagerInterface $entityManager
     * @param UrlManager $urlManager
     */
    public function __construct(TranslatorInterface $translator, EntityManagerInterface $entityManager, UrlManager $urlManager)
    {
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->urlManager = $urlManager;
    }

    /**
     * Validate
     *
     * @param mixed $value
     * @param Constraint $constraint
     * @return null|string
     */
    public function validate($value, Constraint $constraint): ?string
    {
        /** @var $form Form */
        $form = $this->context->getRoot();
        $parentEntity = method_exists($form, 'getNormData') ? $form->getNormData() : NULL;
        $urlPost = method_exists($this->context->getObject()->getParent(), 'getNormData') ? $this->context->getObject()->getParent()->getNormData() : NULL;

        if ($urlPost instanceof SeoEntities\Url && $parentEntity && $value) {

            $existingUrl = true;
            $session = new Session();

            try {
                $existingUrl = $this->urlManager->getExistingUrl($urlPost, $session->get('adminWebsite'), $parentEntity);
            } catch (\Exception $exception) {
                $session->getFlashBag()->add('error', $exception->getMessage());
            }

            if (is_bool($existingUrl) && $existingUrl || $existingUrl && $urlPost->getId() !== $existingUrl->getId()) {
                return $this->context->buildViolation(rtrim($this->translator->trans("Cette URL existe déjà !!", [], 'validators_cms'), '<br/>'))
                    ->addViolation();
            }
        }

        return NULL;
    }
}