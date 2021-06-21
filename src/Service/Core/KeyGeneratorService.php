<?php

namespace App\Service\Core;

/**
 * KeyGeneratorService
 *
 * To generate token, password ...
 *
 * @property string $uppers
 * @property string $lowers
 * @property string $specialCharacters
 * @property string $numbers
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class KeyGeneratorService
{
    private $uppers = '';
    private $lowers = '';
    private $specialCharacters = '';
    private $numbers = '';

    /**
     * Generate
     *
     * @param int $uppers
     * @param int $lowers
     * @param int $specialCharacters
     * @param int $numbers
     * @return string
     */
    public function generate(int $uppers = 0, int $lowers = 0, int $specialCharacters = 0, int $numbers = 0)
    {
        if ($uppers > 0) {
            $this->setUppers($uppers);
        }

        if ($lowers > 0) {
            $this->setLowers($lowers);
        }

        if ($specialCharacters > 0) {
            $this->setSpecialCharacters($specialCharacters);
        }

        if ($numbers > 0) {
            $this->setNumbers($numbers);
        }

        return str_shuffle($this->uppers . $this->lowers . $this->specialCharacters . $this->numbers);
    }

    /**
     * Set uppers
     *
     * @param int $length
     */
    private function setUppers(int $length)
    {
        $uppers = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $this->uppers = $this->randChars($length, $uppers);
    }

    /**
     * Set lowers
     *
     * @param int $length
     */
    private function setLowers(int $length)
    {
        $lowers = 'abcdefghijklmnopkrstuvwxyz';
        $this->lowers = $this->randChars($length, $lowers);
    }

    /**
     * Set special characters
     *
     * @param int $length
     */
    private function setSpecialCharacters(int $length)
    {
        $specialCharacters = '&~@!$?#*(){}_';
        $this->specialCharacters = $this->randChars($length, $specialCharacters);
    }

    /**
     * Set numbers
     *
     * @param int $length
     */
    private function setNumbers(int $length)
    {
        $numbers = '1234567890';
        $this->numbers = $this->randChars($length, $numbers);
    }

    /**
     * Generate rand string for password
     *
     * @param int $length
     * @param string $string
     * @return false|string
     */
    private function randChars(int $length, string $string)
    {
        $string = str_shuffle($string);
        $string = substr($string, 0, $length);
        return $string;
    }
}