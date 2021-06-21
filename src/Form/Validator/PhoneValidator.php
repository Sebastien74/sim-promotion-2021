<?php

namespace App\Form\Validator;

use libphonenumber\PhoneNumberUtil;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * ZipCodeValidator
 *
 * Check if is valid zip code
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class PhoneValidator extends ConstraintValidator
{
    private $translator;

    /**
     * ZipCodeValidator constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Validate
     *
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value) {

            $isValid = $this->isPhone($value);

            if (!$isValid) {
                $message = $this->translator->trans('This value is not valid phone.' . $isValid, [], 'validators');
                $this->context->buildViolation($message)->addViolation();
            }
        }
    }

    /**
     * Check if is phone number
     *
     * @param mixed $value
     * @return bool
     */
    private function isPhone($value): bool
    {
        foreach (Countries::getNames() as $code => $name) {
            $phoneUtil = PhoneNumberUtil::getInstance();
            try {
                if ($phoneUtil->parse($value, strtoupper($code))) {
                    return true;
                }
            } catch (\Exception $exception) {
            }
        }

        return false;
    }
}