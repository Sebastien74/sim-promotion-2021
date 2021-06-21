<?php

namespace App\Twig\Core;

use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * NonceRuntime
 *
 * Generates a random nonce parameter for XSS attacks.
 *
 * @property string|null $nonce
 * @property Request $request
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class NonceRuntime implements RuntimeExtensionInterface
{
    private $nonce;
    private $request;

    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack->getMasterRequest();
    }

    /**
     * Generates a random nonce parameter for XSS attacks.
     *
     * @return string
     * @throws Exception
     */
    public function getNonce(): string
    {
        if (!$this->nonce) {
            $this->nonce = base64_encode(random_bytes(20));
            $this->request->getSession()->set('app_nonce', $this->nonce);
        }

        return $this->nonce;
    }
}