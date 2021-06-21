<?php

namespace App\Service\Content;

use App\Entity\Core\Website;

/**
 * CryptService
 *
 * Manage string encryption
 *
 * @property string $secretKey
 * @property string $secretIv
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CryptService
{
    private $secretKey = "fc58fd900e20f8f9bfc5af9ac8a5c247";
    private $secretIv = "2y10nlpXG3AbjE4Rt72AkKZRVu3IdRJZ395JXjlM05Wd4StMG7efwqi";

    /**
     * Encrypt or decrypt a string
     *
     * @param Website $website
     * @param string $string
     * @param string $action : e -> Encrypt, d -> decrypt
     *
     * @return String
     */
    function execute(Website $website, $string, $action = 'e')
    {
        $api = $website->getApi();
        $secretKey = $api->getSecuritySecretKey() ? $api->getSecuritySecretKey() : $this->secretKey;
        $secretIv = $api->getSecuritySecretIv() ? $api->getSecuritySecretIv() : $this->secretIv;

        $output = false;
        $encryptMethod = "AES-256-CBC";
        $key = hash('sha256', $secretKey);
        $iv = substr(hash('sha256', $secretIv), 0, 16);

        if ($action == 'e') {
            $output = base64_encode(openssl_encrypt($string, $encryptMethod, $key, 0, $iv));
        } elseif ($action == 'd') {
            $output = openssl_decrypt(base64_decode($string), $encryptMethod, $key, 0, $iv);
        }

        return $output;
    }
}