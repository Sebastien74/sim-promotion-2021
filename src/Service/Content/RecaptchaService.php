<?php

namespace App\Service\Content;

use App\Entity\Core\Website;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * RecaptchaService
 *
 * Manage recaptcha security post
 *
 * @property CryptService $cryptService
 * @property TranslatorInterface $translator
 * @property Request $request
 * @property EntityManagerInterface $entityManager
 * @property KernelInterface $kernel
 * @property Session $session
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class RecaptchaService
{
    private $cryptService;
    private $translator;
    private $request;
    private $entityManager;
    private $kernel;
    private $session;

    /**
     * RecaptchaService constructor.
     *
     * @param CryptService $cryptService
     * @param TranslatorInterface $translator
     * @param RequestStack $requestStack
     * @param EntityManagerInterface $entityManager
     * @param KernelInterface $kernel
     */
    public function __construct(
        CryptService $cryptService,
        TranslatorInterface $translator,
        RequestStack $requestStack,
        EntityManagerInterface $entityManager,
        KernelInterface $kernel)
    {
        $this->cryptService = $cryptService;
        $this->translator = $translator;
        $this->request = $requestStack->getCurrentRequest();
        $this->entityManager = $entityManager;
        $this->kernel = $kernel;
        $this->session = new Session();
    }

    /**
     * Check if is valid POST
     *
     * @param Website $website
     * @param mixed $entity
     * @param FormInterface $form
     * @param string|null $email
     * @return bool
     * @throws Exception
     */
    public function execute(Website $website, $entity, FormInterface $form, string $email = NULL)
    {
        $post = filter_input_array(INPUT_POST)[$form->getName()];
        $formSecurityKey = $entity->getSecurityKey();
        $this->setSecurityKeys($website);

        if (!$entity->getRecaptcha()) {
            return true;
        }

        if (!empty($post['field_ho']) && empty($post['field_ho_entitled'])) {
            $honeyPost = $this->cryptService->execute($website, $post['field_ho'], 'd');
            if (urldecode($honeyPost) == $formSecurityKey) {
                return true;
            }
        }

        $this->session->getFlashBag()->add('error_form', $this->translator->trans("Erreur de sécurité !! Rechargez la page et réessayez.", [], 'front_form'));

        $logger = new Logger('SPAM');
        $logger->pushHandler(new RotatingFileHandler($this->kernel->getLogDir() . '/spams.log', 10, Logger::INFO));

        if ($email) {
            $logger->alert('Recaptcha security. This email seems to be spam :' . $email);
        } else {
            $logger->alert('Recaptcha security. IP spam :' . $this->request->getClientIp());
        }

        return false;
    }

    /**
     * Set security keys if not generated
     *
     * @param Website $website
     * @return object
     * @throws Exception
     */
    private function setSecurityKeys(Website $website)
    {
        $flush = false;
        $api = $website->getApi();
        $securityKey = $api->getSecuritySecretKey();
        $securityIv = $api->getSecuritySecretIv();

        if (!$securityKey) {
            $key = base64_encode(uniqid() . password_hash(uniqid(), PASSWORD_BCRYPT) . random_bytes(10));
            $api->setSecuritySecretKey(substr(str_shuffle($key), 0, 45));
            $flush = true;
        }

        if (!$securityIv) {
            $key = base64_encode(uniqid() . password_hash(uniqid(), PASSWORD_BCRYPT) . random_bytes(10));
            $api->setSecuritySecretIv(substr(str_shuffle($key), 0, 45));
            $flush = true;
        }

        if ($flush) {
            $this->entityManager->persist($api);
            $this->entityManager->flush();
        }

        return (object)[
            'securityKey' => $securityKey,
            'securityIv' => $securityIv
        ];
    }
}