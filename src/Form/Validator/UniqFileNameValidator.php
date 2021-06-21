<?php

namespace App\Form\Validator;

use App\Entity\Media\Media;
use App\Repository\Media\MediaRepository;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * UniqFileNameValidator
 *
 * Check if filename already exist
 *
 * @property string $uploadsPath
 * @property KernelInterface $kernel
 * @property TranslatorInterface $translator
 * @property MediaRepository $mediaRepository
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class UniqFileNameValidator extends ConstraintValidator
{
    private $uploadsPath;
    private $kernel;
    private $translator;
    private $mediaRepository;

    /**
     * UniqFileValidator constructor.
     *
     * @param string $uploadsPath
     * @param KernelInterface $kernel
     * @param TranslatorInterface $translator
     * @param MediaRepository $mediaRepository
     */
    public function __construct(string $uploadsPath, KernelInterface $kernel, TranslatorInterface $translator, MediaRepository $mediaRepository)
    {
        $this->uploadsPath = $uploadsPath;
        $this->kernel = $kernel;
        $this->translator = $translator;
        $this->mediaRepository = $mediaRepository;
    }

    /**
     * Validate
     *
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        /** @var Media $media */
        $entity = $this->context->getRoot()->getData();

        if ($entity instanceof Media) {

            $filename = Urlizer::urlize($entity->getName());
            $existingMedia = $this->mediaRepository->findOneByName($filename);

            if ($existingMedia && $existingMedia !== $entity) {
                $message = $this->translator->trans('Un autre fichier porte déjà ce nom !', [], 'validators_cms') . ' (' . $filename . ')';
                $this->context->buildViolation(rtrim($message, '<br/>'))->addViolation();
            }

            if ($entity->getFilename() && !$entity->getName()) {
                $message = $this->translator->trans('This value should not be blank.', [], 'validators');
                $this->context->buildViolation(rtrim($message, '<br/>'))->addViolation();
            }
        }
    }
}