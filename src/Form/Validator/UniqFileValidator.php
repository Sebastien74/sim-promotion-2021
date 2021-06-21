<?php

namespace App\Form\Validator;

use App\Entity\Core\Website;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * UniqFileValidator
 *
 * Check if file already exist
 *
 * @property string $uploadsPath
 * @property KernelInterface $kernel
 * @property TranslatorInterface $translator
 * @property Website $website
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class UniqFileValidator extends ConstraintValidator
{
    private $uploadsPath;
    private $kernel;
    private $translator;
    private $website;

    /**
     * UniqFileValidator constructor.
     *
     * @param string $uploadsPath
     * @param KernelInterface $kernel
     * @param TranslatorInterface $translator
     * @param RequestStack $requestStack
     */
    public function __construct(string $uploadsPath, KernelInterface $kernel, TranslatorInterface $translator, RequestStack $requestStack)
    {
        $this->uploadsPath = $uploadsPath;
        $this->kernel = $kernel;
        $this->translator = $translator;
        $request = $requestStack->getMasterRequest();
        $session = new Session();
        $this->website = preg_match('/\/admin-' . $_ENV['SECURITY_TOKEN'] . '/', $request->getUri())
            ? $session->get('adminWebsite')
            : $session->get('frontWebsite');
    }

    /**
     * Validate
     *
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        /* @var $constraint UniqFile */

        $values = is_array($value) ? $value : [$value];
        $violation = false;
        $message = '';

        if ($values) {

            foreach ($values as $value) {

                if ($value) {

                    /* @var $value UploadedFile */

                    $originalFilename = pathinfo($value->getClientOriginalName(), PATHINFO_FILENAME);
                    $urlizeFilename = Urlizer::urlize($originalFilename);
                    $filesystem = new Filesystem();
                    $existingFile = $filesystem->exists($this->uploadsPath . '/' . $this->website->getUploadDirname() . '/' . $urlizeFilename . '.' . $value->guessExtension());

                    if ($existingFile) {
                        $violation = true;
                        $message .= $this->translator->trans('Un autre fichier porte déjà ce nom !', [], 'validators_cms') . ' (' . $value->getClientOriginalName() . ')' . '<br/>';
                    }
                }
            }

            if ($violation) {
                $this->context->buildViolation(rtrim($message, '<br/>'))
                    ->addViolation();
            }
        }
    }
}