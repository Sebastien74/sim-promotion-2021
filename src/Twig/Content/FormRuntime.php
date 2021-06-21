<?php

namespace App\Twig\Content;

use App\Entity\Module\Form\ContactForm;
use App\Entity\Module\Form\ContactStepForm;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * FormRuntime
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FormRuntime implements RuntimeExtensionInterface
{
    /**
     * Get contact values send
     *
     * @param ContactForm|ContactStepForm $contact
     * @return array
     */
    function contactValues($contact = NULL)
    {
        $fields = [];

        if ($contact && is_object($contact) && method_exists($contact, 'getContactValues')) {
            foreach ($contact->getContactValues() as $value) {
                $configuration = $value->getConfiguration();
                if ($configuration && $configuration->getSlug()) {
                    $fields[$value->getConfiguration()->getSlug()] = $value;
                }
            }
        }

        return $fields;
    }
}