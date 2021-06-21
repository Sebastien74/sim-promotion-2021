<?php

namespace App\Security;

use App\Entity\Core\Website;
use App\Service\Content\CryptService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * RecaptchaAuthenticator
 *
 * Manage recaptcha security authenticate post
 *
 * @property CryptService $cryptService
 * @property TranslatorInterface $translator
 * @property EntityManagerInterface $entityManager
 * @property KernelInterface $kernel
 * @property Session $session
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class RecaptchaAuthenticator
{
    private $cryptService;
    private $translator;
    private $entityManager;
    private $kernel;
    private $session;

    /**
     * RecaptchaAuthenticator constructor.
     *
     * @param CryptService $cryptService
     * @param TranslatorInterface $translator
     * @param EntityManagerInterface $entityManager
     * @param KernelInterface $kernel
     */
    public function __construct(
        CryptService $cryptService,
        TranslatorInterface $translator,
        EntityManagerInterface $entityManager,
        KernelInterface $kernel)
    {
        $this->cryptService = $cryptService;
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->kernel = $kernel;
        $this->session = new Session();
    }

    /**
     * Check if is valid POST
     *
     * @param Request $request
     * @return bool
     * @throws Exception
     */
    public function execute(Request $request)
    {
        /** @var Website $website */
        $website = $this->entityManager->getRepository(Website::class)->findOneByHost($request->getHost());
        $formSecurityKey = $website->getSecurity()->getSecurityKey();
        $this->setSecurityKeys($website);
        $fieldHo = $request->request->get('field_ho');
        $fieldHoEntitled = $request->request->get('field_ho_entitled');

        if (!empty($fieldHo) && empty($fieldHoEntitled)) {
            $honeyPost = $this->cryptService->execute($website, $fieldHo, 'd');
            if (urldecode($honeyPost) == $formSecurityKey) {
                return true;
            }
        }

        $this->session->getFlashBag()->add('error_form', $this->translator->trans("Erreur de sécurité !! Rechargez la page et réessayez.", [], 'front_form'));

        $logger = new Logger('SECURITY_FORM');
        $logger->pushHandler(new RotatingFileHandler($this->kernel->getLogDir() . '/security-cms.log', 10, Logger::CRITICAL));
        $logger->critical('Recaptcha security. IP register :' . $request->getClientIp());

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