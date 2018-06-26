<?php

require_once 'ValidatorInterface.php';

class EmailValidator implements ValidatorInterface
{
    public function validate(string $data) : bool
    {
        return filter_var($data, FILTER_VALIDATE_EMAIL);
    }
}