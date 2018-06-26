<?php

require_once 'Validator.php';

class PhoneNumberValidator implements Validator
{
    public function validate(string $data) : bool
    {
        preg_match('/^((\d{9})|([+]\d{11}))$/', $data, $match);
        return isset($match[1]) || isset($match[2]);
    }
}